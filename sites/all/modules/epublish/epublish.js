function onChangeWeight(nid) {
  var formObj = document.forms[0];
  formObj['edit[nodes][' + nid + ']'].checked = 1;
}

function onChangeTID(nid) {
  var formObj = document.forms[0];
  formObj['edit[nodes][' + nid + ']'].checked = 1;
}