var replacements = [
  [ /\*/g, '\\*' ],
  [ /#/g, '\\#' ],
  [ /\//g, '\\/' ],
  [ /\(/g, '\\(' ],
  [ /\)/g, '\\)' ],
  [ /\[/g, '\\[' ],
  [ /\]/g, '\\]' ],
  [ /\</g, '&lt;' ],
  [ /\>/g, '&gt;' ],
  [ /_/g, '\\_' ] ]

module.exports = function(string) {
  return replacements.reduce(
    function(string, replacement) {
      return string.replace(replacement[0], replacement[1])
    },
    string) }
