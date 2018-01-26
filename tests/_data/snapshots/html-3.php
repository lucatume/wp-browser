<?php return '<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
	<img src="http://example.com/images/one.jpeg" alt="foo">
	<img src="http://example.com/images/two.jpeg" alt="foo">
	<img src="http://example.com/images/three.jpeg" alt="foo">
	<a href="http://example.com">Home</a>
	<a href="http://example.com/foo">Path</a>
	<a href="http://example.com/foo/bar/baz">Deeper Path</a>
	<a href="/">Relative Home</a>
	<a href="/foo">Relative Path</a>
	<a href="/foo/bar/baz">Deeper Path</a>
	<a href="http://example.com/?foo=bar&bar=baz">Home with query arg</a>
	<a href="http://example.com/foo/?foo=bar&bar=baz">Path with query arg</a>
	<a href="http://example.com/foo/bar/baz/?foo=bar&bar=baz">Deeper Path with query arg</a>
	<a href="/?foo=bar&bar=baz">Relative Home with query arg</a>
	<a href="/foo/?foo=bar&bar=baz">Relative Path with query arg</a>
	<a href="/foo/bar/baz/?foo=bar&bar=baz">Deeper Path with query arg</a>
</body>
</html>';