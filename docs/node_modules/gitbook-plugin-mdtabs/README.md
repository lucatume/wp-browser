# mdtabs

A fork of codetabs that renders markdown in tab body so you have more flexibility.
![Preview](./preview.png)

### Installation

Adds the plugin to your `book.json`, then run `gitbook install` if you are building your book locally.

```js
{
    "plugins": ["mdtabs"]
}
```

### Usage

<pre lang="markdown">
<code>
This is a code block with tabs for each languages:

{% mdtabs title="Python" %}
## can have headers here
```python
msg = "Hello World"
print msg
```
{% mdtab title="JavaScript" %}
```js
var msg = "Hello World";
console.log(msg);
```
{% mdtab title="HTML" %}
```html
<b>Hello World</b>
```
{% endmdtabs %}
</code>
</pre>
