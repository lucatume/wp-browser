<?php return '
<div>
<section>
	<img src="{{url}}/images/one.jpeg" alt="foo">
	<img src="{{url}}/images/two.jpeg" alt="foo">
	<img src="{{url}}/images/three.jpeg" alt="foo">
</section>
<nav>
	<a href="{{url}}">Home</a>
	<a href="{{url}}/foo">Path</a>
	<a href="{{url}}/foo/bar/baz">Deeper Path</a>
	<a href="/">Relative Home</a>
	<a href="/foo">Relative Path</a>
	<a href="/foo/bar/baz">Deeper Path</a>
	<a href="{{url}}/?foo=bar&bar=baz">Home with query arg</a>
	<a href="{{url}}/foo/?foo=bar&bar=baz">Path with query arg</a>
	<a href="{{url}}/foo/bar/baz/?foo=bar&bar=baz">Deeper Path with query arg</a>
	<a href="/?foo=bar&bar=baz">Relative Home with query arg</a>
	<a href="/foo/?foo=bar&bar=baz">Relative Path with query arg</a>
	<a href="/foo/bar/baz/?foo=bar&bar=baz">Deeper Path with query arg</a>
</nav>
<section>
	<img src="http://another.com/images/one.jpeg" alt="foo">
	<img src="http://another.com/images/two.jpeg" alt="foo">
	<img src="http://another.com/images/three.jpeg" alt="foo">
</section>
<nav>
	<a href="http://another.com">Home</a>
	<a href="http://another.com/foo">Path</a>
	<a href="http://another.com/foo/bar/baz">Deeper Path</a>
	<a href="/">Relative Home</a>
	<a href="/foo">Relative Path</a>
	<a href="/foo/bar/baz">Deeper Path</a>
	<a href="http://another.com/?foo=bar&bar=baz">Home with query arg</a>
	<a href="http://another.com/foo/?foo=bar&bar=baz">Path with query arg</a>
	<a href="http://another.com/foo/bar/baz/?foo=bar&bar=baz">Deeper Path with query arg</a>
	<a href="/?foo=bar&bar=baz">Relative Home with query arg</a>
	<a href="/foo/?foo=bar&bar=baz">Relative Path with query arg</a>
	<a href="/foo/bar/baz/?foo=bar&bar=baz">Deeper Path with query arg</a>
</nav>
</div>';