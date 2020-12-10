function showOrHideHint(event, showArea, hintArea) {
  if( typeof showOrHideHint.wasIn == 'undefined' ) {
      showOrHideHint.wasIn = false;
  }
  let hintRect = $(hintArea)[0].getBoundingClientRect();
  let showRect = $(showArea)[0].getBoundingClientRect();
  if (((event.clientX > hintRect.left) && (event.clientX < hintRect.right) && (event.clientY > hintRect.top) && (event.clientY < hintRect.bottom)) ||
      ((event.clientX > showRect.left) && (event.clientX < showRect.right) && (event.clientY > showRect.top) && (event.clientY < showRect.bottom))) {
    if (!showOrHideHint.wasIn) {
      $(showArea).stop();
      $(hintArea).hide();
      $(showArea).css("opacity", 1);
      $(showArea).show();
      showOrHideHint.wasIn = true;
    }
  } else {
    if (showOrHideHint.wasIn) {
      $(showArea).fadeOut({
          duration: 3000,
          complete: function() {
            $(hintArea).show();
          },
        });
      showOrHideHint.wasIn = false;
    }
  }
}

function hideThenShowHint(showArea, hintArea) {
  $(showArea).stop();
  $(hintArea).hide();
  $(showArea).css("opacity", 1);
  $(showArea).show();
  $(showArea).fadeOut({
      duration: 3000,
      complete: function() {
        $(hintArea).show();
      },
    });
  showOrHideHint.wasIn = false;
}
