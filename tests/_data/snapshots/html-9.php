<?php return '
<div data-id="{{one}}">
	<p class="prefix-{{one}}" id="prefix-{{one}}" data-prefix="prefix-{{one}}" data-more="another_prefix-{{one}}">
		Some data {{one}} prefix-{{one}} word-prefix- another_prefix- another_prefix-word
	</p>
</div>
<div data-id="{{two}}">
	<p class="prefix-{{two}}" id="prefix-{{two}}" data-prefix="prefix-{{two}}" data-more="another_prefix-{{two}}">
	 	Some data {{two}} prefix-{{two}} prefix- word-prefix- another_prefix- another_prefix-word
	</p>
</div>
<div data-id="{{one}}-postfix">
	<p class="{{one}}-postfix" id="{{one}}-postfix" data-bar="{{one}}-postfix" data-more="{{one}}-another_postfix">
		Some data {{one}} {{one}}-postfix postfix word-postfix word-another_postfix
	</p>
</div>
<div data-id="{{two}}-postfix">
	<p class="{{two}}-postfix" id="{{two}}-postfix" data-foo="{{two}}-postfix" data-more="{{two}}-another_postfix">
	 	Some data {{two}} {{two}}-postfix postfix word-postfix word-another_postfix
	</p>
</div>';
