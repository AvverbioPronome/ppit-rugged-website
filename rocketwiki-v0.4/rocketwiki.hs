
import Text.ParserCombinators.Parsec
import Control.Applicative ((<$>), (<*>))
import Data.List (intercalate)


-----------------------------------------------------
-- abstract combinators (not shipping with Parsec) --
-----------------------------------------------------

manyTill1 p ending = do
  result <- manyTill p ending
  if null result
    then unexpected ""
    else return result


 ---------------------------------------------
-- character classes and new-line sequence --
---------------------------------------------

-- space chars
spaceChars :: [Char]
spaceChars = " \t"

-- matches and returns any space char
sp :: Parser Char
sp = oneOf spaceChars

-- chars for line break
breakChars :: [Char]
breakChars = "\r\n"

-- characters used for markup/formatting of text
markupChars :: [Char]
markupChars = "@$=-#{};*/_+%^~|&[],'\"<>()"

-- alphanumerics and underscore
alphanumericChars :: [Char]
alphanumericChars =
  "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"

-- alphanumerics and underscore
classChars :: [Char]
classChars = alphanumericChars ++ "_"

-- chars appearing in an Uniform Resource Identifier, except square brackets
uriChars :: [Char]
uriChars = alphanumericChars ++ "-._~%:/?#@!$&'()*+,;="

-- chars allowed in local part of an eMail address
emailLocalPartChars :: [Char]
emailLocalPartChars = alphanumericChars ++ ".!#$%&'*+-/=?^_`{|}~"

-- chars allowed in domain part of an eMail address
emailDomainPartChars :: [Char]
emailDomainPartChars = alphanumericChars ++ "-."

-- any white space
spaceOrBreakChars = spaceChars ++ breakChars

-- matches a LF, CRLF or CR sequence and returns it
nl :: Parser String
nl = string "\n" <|> try (string "\r\n") <|> string "\r"

-- matches a LF, CRLF or CR sequence and returns it
-- or matches the end-of-file and returns an empty string
eol :: Parser String
eol = nl <|> (eof >> return "")


-------------------
-- basic parsers --
-------------------

-- matches the empty line and returns nothing
emptyLine :: Parser ()
emptyLine = try $ do
  skipMany sp
  nl
  return ()

-- skips an empty line if possible,
-- returns nothing and is always successful
optEmptyLine :: Parser ()
optEmptyLine = option () emptyLine

-- matches and returns an identifier
-- consisting of alpha-numeric characters and underscores
classIdentifier :: Parser String
classIdentifier = try $ many1 $ oneOf classChars

-- matches and returns an Uniform Resource Locator not containing "!"
uri :: Parser String
uri = try $ many1 $ oneOf $ filter (/= '!') uriChars

-- matches and returns an eMail address
email :: Parser String
email = try $ do
  local <- many1 $ oneOf emailLocalPartChars
  char '@'
  domain <- many1 $ oneOf emailDomainPartChars
  return $ local ++ "@" ++ domain

-- parses a character (or many space characters) as long as they are not
-- included in the list of exceptions and returns a String containing the
-- char or its HTML entity
normalCharExcept :: [Char] -> Parser String
normalCharExcept exceptions =
  (many1 sp >> return " ") <|>
  (htmlEscape <$> noneOf (breakChars ++ exceptions))

-- parses a character (or many space characters) and returns a String
-- containing the char or its HTML entity
normalChar :: Parser String
normalChar = normalCharExcept []

-- Modifies a Parser (returning String) in a way that it first reads ALL
-- chars which can't have a special meaning, collapsing its spaces and
-- applying HTML entities. If  the given Parser is used. If it does not
-- match, ONE char not being newline (or many space characters) will be
-- read and returned with spaces collapsed and HTML entities applied.
orNormal :: Parser String -> Parser String
orNormal parser = nonMarkupChars <|> parser <|> normalChar
  where nonMarkupChars = concat <$> many1 (normalCharExcept markupChars)

-- representation of a Char in HTML
htmlEscape :: Char -> String
htmlEscape '<' = "&lt;"
htmlEscape '>' = "&gt;"
htmlEscape '"' = "&quot;"
htmlEscape '&' = "&amp;"
htmlEscape  c  = [c]


----------------------------------------
-- Help functions for security checks --
----------------------------------------

isAllowedUri str = result
  where
    parseResult = parse parser "" str
    Right result = parseResult
    parser = do
      proto <- option "" $ try $ manyTill anyChar (char ':')
      return $ any (proto ==) ["", "http", "https", "ftp"]


-----------------
-- wiki parser --
-----------------

-- a complete wiki document
document :: Parser String
document = do
  contents <- concat <$> many section
  skipMany $ oneOf spaceOrBreakChars
  eof
  return contents

-- section of a wiki document
section :: Parser String
section = choice [
  gap, divSection, spanSection,
  heading, seperator, unnumberedList, numberedList,
  paragraph ]

-- empty lines, when two or more returns "<p>&nbsp;...</p>", otherwise ""
gap :: Parser String
gap = try $ do
  emptyLine
  fillers <- many (emptyLine >> return "&nbsp;")
  if null fillers
    then return ""
    else return $ "<p>" ++ intercalate "<br/>\n" fillers ++ "</p>\n"

divSection :: Parser String
divSection = try $ do
    skipMany sp
    string "@@"
    skipMany sp
    divId <- classIdentifier
    skipMany sp
    char ':'
    skipMany sp
    eol
    contents <- endedParagraphContents <|> endedSections
    return $
      "<div id=\"wiki_" ++ divId ++ "\" class=\"wiki_" ++ divId ++
      "\">" ++ contents ++ "</div>\n"
  where
    endedParagraphContents = try $ do
      contents <- paragraphContents
      ending
      return contents
    endedSections = concat <$> manyTill section ending
    ending = try $ do
      skipMany sp
      string "@@"
      skipMany sp
      eol
      optEmptyLine
      return ()


spanSection :: Parser String
spanSection = try $ do
  skipMany sp
  string "$$"
  skipMany sp
  className <- classIdentifier
  skipMany sp
  char ':'
  skipMany sp
  eol
  contents <- concat <$> manyTill section ( do
    skipMany sp
    string "$$"
    skipMany sp
    eol
    optEmptyLine )
  return $
    "<span class=\"wiki_" ++ className ++ "\">" ++ contents ++ "</span>\n"


-- heading
heading :: Parser String
heading = try $ do
  skipMany sp
  delim <- many1 (char '=')
  let tag | length delim == 1 = "h4"
          | length delim == 2 = "h3"
          | length delim == 3 = "h2"
          | otherwise         = "h1"
  skipMany sp
  contents <- textTill $ do
    try (skipMany sp >> char '=')
    -- point of no return
    string (tail delim)
    skipMany sp
    eol
    return ()
  optEmptyLine
  return $ "<" ++ tag ++ ">" ++ contents ++ "</" ++ tag ++ ">\n"

-- horizontal rule
seperator :: Parser String
seperator = try $ do
  skipMany sp
  string "---"
  skipMany (char '-')
  skipMany sp
  eol
  optEmptyLine
  return "<hr/>\n"

-- <ul><li> and <ol><li> lists
unnumberedList :: Parser String
numberedList   :: Parser String
abstractList   :: Char -> String -> String -> Parser String
unnumberedList = abstractList '-' "<ul>" "</ul>\n"
numberedList   = abstractList '#' "<ol>" "</ol>\n"
abstractList keyChar open close = try $ do
    result <- listLevel 1
    optEmptyLine
    return result
  where
    enclose str = "<li>" ++ str ++ "</li>\n"
    listLevel l = try $ do
        entries <- many1 listEntry
        return $ open ++ concat (map enclose entries) ++ close
      where
        listEntry = try $ do
          skipMany sp
          count l $ char keyChar
          notFollowedBy $ char keyChar
          skipMany sp
          result <- text
          eol
          children <- option "" $ try $ listLevel (l+1)
          return $ result ++ children

-- wiki paragraph
paragraph :: Parser String
paragraph = do
  contents <- paragraphContents
  return $ "<p>" ++ contents ++ "</p>\n"

-- wiki paragraph without <p>...</p>
paragraphContents :: Parser String
paragraphContents = try $ do
  contents <- text
  eol
  optEmptyLine
  fillers <- concat <$> (map (const "<br/>&nbsp;") <$> many emptyLine)
  return $ contents ++ fillers

-- wiki text (including simple line breaks)
text :: Parser String
text = textImpl many1
textTill :: Parser () -> Parser String
textTill ending = textImpl (\p -> manyTill1 p ending)
textTillStr :: String -> Parser String
textTillStr str = textTill (try (string str) >> return ())
textImpl :: (Parser String -> Parser [String]) -> Parser String
textImpl manyVariant = try (concat <$> manyVariant textElement)
  where
    textElement = orNormal $ choice [
      escapedText, commentText, taggedText, spanText,
      singleEnglishQuotedText, doubleEnglishQuotedText, specialChar,
      imageOrLink, lineBreak ]

-- simple line break not followed by certain patterns
lineBreak :: Parser String
lineBreak = try $ do
    skipMany sp
    nl
    unacceptable <- (do
        choice [ eof, emptyLine, divOrSpanSectionMarker,
          listBegin, heading >> return () ]
        return True
      ) <|> return False
    if unacceptable then unexpected "" else return "<br/>\n"
  where
    divOrSpanSectionMarker = try $ do
      skipMany sp
      try (string "@@") <|> string "$$"
      skipMany sp
      eol <|> do
        classIdentifier
        skipMany sp
        char ':'
        skipMany sp
        eol
      return ()
    listBegin = try $ do
      skipMany sp
      try (skipMany1 (char '-')) <|> skipMany1 (char '#')
      skipMany sp
      noneOf spaceOrBreakChars
      return ()

escapedText :: Parser String
escapedText = try $ do
  string "{{"
  concat <$> manyTill (orNormal lineBreak) (try $ string "}}")

commentText :: Parser String
commentText = try $ do
  string ";;"
  concat <$> manyTill (orNormal lineBreak) (try $ string ";;")
  return ""

taggedText :: Parser String
taggedText = choice [
    taggedWith "**" "b",
    taggedWith "//" "i",
    taggedWith "__" "u",
    taggedWith "++" "big",
    taggedWith "%%" "small",
    taggedWith "^^" "sup",
    taggedWith "~~" "sub",
    taggedWith "||" "tt",
    taggedWith "&&" "nobr" ]
  where
    taggedWith wikiTag htmlTag = try $ do
      string wikiTag
      contents <- textTillStr wikiTag
      return $ "<" ++ htmlTag ++ ">" ++ contents ++ "</" ++ htmlTag ++ ">"

spanText :: Parser String
spanText = try $ do
  string "$$"
  skipMany sp
  className <- classIdentifier
  skipMany sp
  char ':'
  contents <- textTillStr "$$"
  return $ "<span class=\"wiki_" ++ className ++ "\">" ++
           contents ++ "</span>"

imageOrLink :: Parser String
imageOrLink = try $ do
    char '['
    skipMany sp
    classAttr <- try (do
        className <- classIdentifier
        skipMany sp
        char ':'
        ( do
            try $ string "//"
            unexpected ""
          ) <|> ( do
              skipMany sp
              return $ " class=\"wiki_" ++ className ++ "\""
            )
      ) <|> return ""
    try (emailLink classAttr) <|>
      try (image classAttr) <|>
      (link classAttr)
  where
    emailLink :: String -> Parser String
    emailLink classAttr = do
      address <- email
      contents <-
        try (skipMany1 sp >> textTillStr "]") <|>
        (skipMany sp >> char ']' >> return address)
      return $
        "<a" ++ classAttr ++ " href=\"mailto:" ++ address ++ "\">" ++
        contents ++ "</a>"
    image :: String -> Parser String
    image classAttr = do
      url <- try $ do
        base <- concat <$> (
          many1 $ try $ (++ ".") <$> (
          manyTill (oneOf uriChars) (
            (oneOf (']':spaceOrBreakChars) >> unexpected "") <|> char '.'
          ) ) )
        ext <- choice $ try <$> ( string <$> [ "jpg", "jpeg", "gif", "png" ] )
        notFollowedBy $ noneOf (']':spaceOrBreakChars)
        return $ base ++ ext
      True <- return $ isAllowedUri url
      skipMany sp
      altAttr <- option "" $ try $ do
        alt <- many1 $ noneOf (']':spaceOrBreakChars)
        return $ " alt=\"" ++ alt ++ "\""
      char ']'
      return $
        "<img" ++ classAttr ++ " src=\"" ++ url ++ "\"" ++ altAttr ++ "/>"
    link :: String -> Parser String
    link classAttr = do
      url <- uri
      True <- return $ isAllowedUri url
      option () (char '!' >> return ())
      contents <- try (skipMany1 sp >> image classAttr) <|>
                  try (skipMany1 sp >> textTillStr "]") <|>
                  (skipMany sp >> char ']' >> return url)
      return $
        "<a" ++ classAttr ++ " href=\"" ++ url ++ "\">" ++
        contents ++ "</a>"

singleEnglishQuotedText :: Parser String
singleEnglishQuotedText = try $ do
  string "\"\"\""
  contents <- textTillStr "\"\"\""
  return $ "&lsquo;" ++ contents ++ "&rsquo;"

doubleEnglishQuotedText :: Parser String
doubleEnglishQuotedText = try $ do
  string "\"\""
  contents <- textTillStr "\"\""
  return $ "&ldquo;" ++ contents ++ "&rdquo;"

specialChar :: Parser String
specialChar = choice [
    -- german umlauts and SZ ligature
    stringMapping "{AE}"   "&Auml;",
    stringMapping "{Ae}"   "&Auml;",
    stringMapping "{ae}"   "&auml;",
    stringMapping "{OE}"   "&Ouml;",
    stringMapping "{Oe}"   "&Ouml;",
    stringMapping "{oe}"   "&ouml;",
    stringMapping "{UE}"   "&Uuml;",
    stringMapping "{Ue}"   "&Uuml;",
    stringMapping "{ue}"   "&uuml;",
    stringMapping "{ss}"   "&szlig;",
    stringMapping "{sz}"   "&szlig;",
    -- spaces and dashes
    stringMapping "{--}"   "&ndash;",
    stringMapping "{---}"  "&mdash;",
    stringMapping "{__}"   "&ensp;",
    stringMapping "{___}"  "&emsp;",
    -- fractions
    stringMapping "{1/4}"  "&frac14;",
    stringMapping "{1/2}"  "&frac12;",
    stringMapping "{3/4}"  "&frac34;",
    -- mathematical symbols
    stringMapping "{+}"    "+",
    stringMapping "{-}"    "&minus;",
    stringMapping "{*}"    "&times;",
    stringMapping "{x}"    "&times;",
    stringMapping "{/}"    "&divide;",
    stringMapping "{=}"    "=",
    stringMapping "{%0}"   "&permil;",
    stringMapping "{%O}"   "&permil;",
    stringMapping "{%o}"   "&permil;",
    -- other symbols
    stringMapping "{EUR}"  "&euro;",
    stringMapping "{EURO}" "&euro;",
    stringMapping "{Eur}"  "&euro;",
    stringMapping "{Euro}" "&euro;",
    stringMapping "{eur}"  "&euro;",
    stringMapping "{euro}" "&euro;",
    stringMapping "{C}"    "&copy;",
    stringMapping "{c}"    "&copy;",
    stringMapping "{R}"    "&reg;",
    stringMapping "{r}"    "&reg;",
    stringMapping "{TM}"   "&trade;",
    stringMapping "{tm}"   "&trade;",
    stringMapping "{.}"    "&middot;",
    stringMapping "{o}"    "&bull;",
    stringMapping "{...}"  "&hellip;",
    stringMapping "{'}"    "&prime;",
    stringMapping "{''}"   "&Prime;",
    stringMapping "{DEG}"  "&deg;",
    stringMapping "{Deg}"  "&deg;",
    stringMapping "{deg}"  "&deg;",
    -- quotes (except english)
    stringMapping ",,,"    "&sbquo;",
    stringMapping ",,"     "&bdquo;",
    stringMapping "'''"    "&lsquo;",
    stringMapping "''"     "&ldquo;",
    stringMapping "<<<"    "&lsaquo;",
    stringMapping "<<"     "&laquo;",
    stringMapping ">>>"    "&rsaquo;",
    stringMapping ">>"     "&raquo;",
    -- arrows
    stringMapping "->"     "&rarr;",
    stringMapping "=>"     "&rArr;",
    stringMapping "<-"     "&larr;",
    stringMapping "<="     "&lArr;",
    stringMapping "<->"    "&harr",
    stringMapping "<=>"    "&hArr",
    -- flow control characters
    stringMapping "(_)"    "&nbsp;",
    stringMapping "(-)"    "&shy;" ]
  where
    stringMapping wikiEntity htmlEntity = try $ do
      string wikiEntity
      return htmlEntity


main = interact wikiParse

wikiParse str
  | success parseResult = html
  | otherwise           = "<!-- ERROR -->"
  where
    parseResult = parse document "" str
    Left errobj = parseResult
    Right html  = parseResult
    success (Left  _) = False
    success (Right _) = True
 
