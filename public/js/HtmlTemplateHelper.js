// given a template, replace {variable} with corresponding
// variable field within data object.
// also, detech DOM if data-condition is evaluate to false
function populateHtmlTemplateWithData(template, data, debug = false)
{
  let result = template;
  // replace {key} with corresponding value in 'data' object
  for (key in data) {
    let val = data[key];
    if (typeof val == 'string') {
      val = val.replace(/"/g, '&quot;').replace(/'/g, '&apos;').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
    result = result.replace(new RegExp('{' + key + '}', 'g'), val);
  }
  // parse the result
  let dom = new DOMParser().parseFromString(result, "text/xml");
  // conditionally determine if any DOM should be detached.
  $(dom).find('[data-condition]').each(function (index) {
    let predicate = eval($(this).data('condition'));
    if (debug) { console.log($(this).data('condition') + " -> " + (predicate ? "true" : "false")); }
    if (predicate) {
      $(this).removeAttr('data-condition');
    } else {
      $(this).detach();
    }
  });
  return dom.documentElement.outerHTML;
}
