function asRadioClick(obj) {
  var key = obj.getAttribute("data-key");
  var value = obj.getAttribute("data-value");

  //disable all other fields
  var fields = document.querySelectorAll("[data-key=" + key + "]");
  var idx;
  for (idx=0; idx < fields.length; ++idx) {
    fields[idx].removeAttribute("data-selected");
  }
  obj.setAttribute("data-selected","1");

  document.getElementsByName(key)[0].setAttribute("value",value);
}
