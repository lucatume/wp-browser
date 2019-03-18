require('tape')('README', function(test)  {
  var escape = require('./');
  test.deepEqual(escape('#1! We\'re #1!'), '\\#1! We\'re \\#1!', 'line 2');
  test.end();
});
