/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!********************************!*\
  !*** ./src/js/uhp-frontend.js ***!
  \********************************/
(function () {
  let supportsPassive = false;
  try {
    const opts = Object.defineProperty({}, 'passive', {
      get: function () {
        supportsPassive = true;
      }
    });
    window.addEventListener('testPassive', null, opts);
    window.removeEventListener('testPassive', null, opts);
  } catch (e) {}
  function init() {
    let input_begin = '';
    let keydowns = {};
    let lastKeyup = null;
    let lastKeydown = null;
    let keypresses = [];
    let modifierKeys = [];
    let correctionKeys = [];
    let lastMouseup = null;
    let lastMousedown = null;
    let mouseclicks = [];
    let mousemoveTimer = null;
    let lastMousemoveX = null;
    let lastMousemoveY = null;
    let mousemoveStart = null;
    let mousemoves = [];
    let touchmoveCountTimer = null;
    let touchmoveCount = 0;
    let lastTouchEnd = null;
    let lastTouchStart = null;
    let touchEvents = [];
    let scrollCountTimer = null;
    let scrollCount = 0;
    const correctionKeyCodes = ['Backspace', 'Delete', 'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Home', 'End', 'PageUp', 'PageDown'];
    const modifierKeyCodes = ['Shift', 'CapsLock'];
    const forms = document.querySelectorAll('form[method=post]');
    for (let i = 0; i < forms.length; i++) {
      const form = forms[i];
      const formAction = form.getAttribute('action');

      // Ignore forms that POST directly to other domains; these could be things like payment forms.
      if (formAction) {
        // Check that the form is posting to an external URL, not a path.
        if (formAction.indexOf('http://') == 0 || formAction.indexOf('https://') == 0) {
          if (formAction.indexOf('http://' + window.location.hostname + '/') != 0 && formAction.indexOf('https://' + window.location.hostname + '/') != 0) {
            continue;
          }
        }
      }
      form.addEventListener('submit', function () {
        let bkp = prepare_timestamp_array_for_request(keypresses);
        let bmc = prepare_timestamp_array_for_request(mouseclicks);
        let bte = prepare_timestamp_array_for_request(touchEvents);
        let bmm = prepare_timestamp_array_for_request(mousemoves);
        let input_fields = {
          // When did the user begin entering any input?
          'bib': input_begin,
          // When was the form submitted?
          'bfs': Date.now(),
          // How many keypresses did they make?
          'bkpc': keypresses.length,
          // How many mouseclicks did they make?
          'bmcc': mouseclicks.length,
          // How many times did they move the mouse?
          'bmmc': mousemoves.length,
          // How quickly did they move the mouse, and how long between moves?
          'bmm': bmm,
          // How quickly did they press a sample of keys, and how long between them?
          'bkp': bkp,
          // How quickly did they click the mouse, and how long between clicks?
          'bmc': bmc,
          // When did they press modifier keys (like Shift or Capslock)?
          'bmk': modifierKeys.join(';'),
          // When did they correct themselves? e.g., press Backspace, or use the arrow keys to move the cursor back
          'bck': correctionKeys.join(';'),
          // How many times did they scroll?
          'bsc': scrollCount,
          // How many times did they move around using a touchscreen?
          'btmc': touchmoveCount,
          // How quickly did they perform touch events, and how long between them?
          'bte': bte,
          // How many touch events were there?
          'btec': touchEvents.length
        };
        for (const field_name in input_fields) {
          // check if the input field already exists
          let existing_field = this.querySelector('input[name="uhp_' + field_name + '"]');
          if (existing_field) {
            existing_field.value = input_fields[field_name];
          } else {
            let field = document.createElement('input');
            field.setAttribute('type', 'hidden');
            field.setAttribute('name', 'uhp_' + field_name);
            field.setAttribute('value', input_fields[field_name]);
            this.appendChild(field);
          }
        }
      }, supportsPassive ? {
        passive: true
      } : false);
      form.addEventListener('keydown', function (e) {
        // If you hold a key down, some browsers send multiple keydown events in a row.
        // Ignore any keydown events for a key that hasn't come back up yet.

        if (e.key in keydowns) {
          return;
        }
        const keydownTime = new Date().getTime();
        keydowns[e.key] = [keydownTime];
        if (!input_begin) {
          input_begin = keydownTime;
        }

        // In some situations, we don't want to record an interval since the last keypress -- for example,
        // on the first keypress, or on a keypress after focus has changed to another element. Normally,
        // we want to record the time between the last keyup and this keydown. But if they press a
        // key while already pressing a key, we want to record the time between the two keydowns.

        const lastKeyEvent = Math.max(lastKeydown, lastKeyup);
        if (lastKeyEvent) {
          keydowns[e.key].push(keydownTime - lastKeyEvent);
        }
        lastKeydown = keydownTime;
      }, supportsPassive ? {
        passive: true
      } : false);
      form.addEventListener('keyup', function (e) {
        if (!(e.key in keydowns)) {
          // This key was pressed before this script was loaded, or a mouseclick happened during the keypress, or...
          return;
        }
        const keyupTime = new Date().getTime();
        if ('TEXTAREA' === e.target.nodeName || 'INPUT' === e.target.nodeName) {
          if (-1 !== modifierKeyCodes.indexOf(e.key)) {
            modifierKeys.push(keypresses.length - 1);
          } else if (-1 !== correctionKeyCodes.indexOf(e.key)) {
            correctionKeys.push(keypresses.length - 1);
          } else {
            // ^ Don't record timings for keys like Shift or backspace, since they
            // typically get held down for longer than regular typing.

            const keydownTime = keydowns[e.key][0];
            const keypress = [];

            // Keypress duration.
            keypress.push(keyupTime - keydownTime);

            // Amount of time between this keypress and the previous keypress.
            if (keydowns[e.key].length > 1) {
              keypress.push(keydowns[e.key][1]);
            }
            keypresses.push(keypress);
          }
        }
        delete keydowns[e.key];
        lastKeyup = keyupTime;
      }, supportsPassive ? {
        passive: true
      } : false);
      form.addEventListener("focusin", function (e) {
        lastKeydown = null;
        lastKeyup = null;
        keydowns = {};
      }, supportsPassive ? {
        passive: true
      } : false);
      form.addEventListener("focusout", function (e) {
        lastKeydown = null;
        lastKeyup = null;
        keydowns = {};
      }, supportsPassive ? {
        passive: true
      } : false);
    }
    document.addEventListener('mousedown', function (e) {
      lastMousedown = new Date().getTime();
    }, supportsPassive ? {
      passive: true
    } : false);
    document.addEventListener('mouseup', function (e) {
      if (!lastMousedown) {
        // If the mousedown happened before this script was loaded, but the mouseup happened after...
        return;
      }
      const now = new Date().getTime();
      const mouseclick = [];
      mouseclick.push(now - lastMousedown);
      if (lastMouseup) {
        mouseclick.push(lastMousedown - lastMouseup);
      }
      mouseclicks.push(mouseclick);
      lastMouseup = now;

      // If the mouse has been clicked, don't record this time as an interval between keypresses.
      lastKeydown = null;
      lastKeyup = null;
      keydowns = {};
    }, supportsPassive ? {
      passive: true
    } : false);
    document.addEventListener('mousemove', function (e) {
      if (mousemoveTimer) {
        clearTimeout(mousemoveTimer);
        mousemoveTimer = null;
      } else {
        mousemoveStart = new Date().getTime();
        lastMousemoveX = e.offsetX;
        lastMousemoveY = e.offsetY;
      }
      mousemoveTimer = setTimeout(function (theEvent, originalMousemoveStart) {
        const now = new Date().getTime() - 500; // To account for the timer delay.

        let mousemove = [];
        mousemove.push(now - originalMousemoveStart);
        mousemove.push(Math.round(Math.sqrt(Math.pow(theEvent.offsetX - lastMousemoveX, 2) + Math.pow(theEvent.offsetY - lastMousemoveY, 2))));
        if (mousemove[1] > 0) {
          // If there was no measurable distance, then it wasn't really a move.
          mousemoves.push(mousemove);
        }
        mousemoveStart = null;
        mousemoveTimer = null;
      }, 500, e, mousemoveStart);
    }, supportsPassive ? {
      passive: true
    } : false);
    document.addEventListener('touchmove', function (e) {
      if (touchmoveCountTimer) {
        clearTimeout(touchmoveCountTimer);
      }
      touchmoveCountTimer = setTimeout(function () {
        touchmoveCount++;
      }, 500);
    }, supportsPassive ? {
      passive: true
    } : false);
    document.addEventListener('touchstart', function (e) {
      lastTouchStart = new Date().getTime();
    }, supportsPassive ? {
      passive: true
    } : false);
    document.addEventListener('touchend', function (e) {
      if (!lastTouchStart) {
        // If the touchstart happened before this script was loaded, but the touchend happened after...
        return;
      }
      const now = new Date().getTime();
      const touchEvent = [];
      touchEvent.push(now - lastTouchStart);
      if (lastTouchEnd) {
        touchEvent.push(lastTouchStart - lastTouchEnd);
      }
      touchEvents.push(touchEvent);
      lastTouchEnd = now;

      // Don't record this time as an interval between keypresses.
      lastKeydown = null;
      lastKeyup = null;
      keydowns = {};
    }, supportsPassive ? {
      passive: true
    } : false);
    document.addEventListener('scroll', function (e) {
      if (scrollCountTimer) {
        clearTimeout(scrollCountTimer);
      }
      scrollCountTimer = setTimeout(function () {
        scrollCount++;
      }, 500);
    }, supportsPassive ? {
      passive: true
    } : false);
  }
  ;

  /**
  * For the timestamp data that is collected, don't send more than `limit` data points in the request.
  * Choose a random slice and send those.
  */
  function prepare_timestamp_array_for_request(a, limit) {
    if (!limit) {
      limit = 100;
    }
    var rv = '';
    if (a.length > 0) {
      var random_starting_point = Math.max(0, Math.floor(Math.random() * a.length - limit));
      for (var i = 0; i < limit && i < a.length; i++) {
        rv += a[random_starting_point + i][0];
        if (a[random_starting_point + i].length >= 2) {
          rv += "," + a[random_starting_point + i][1];
        }
        rv += ";";
      }
    }
    return rv;
  }
  if (document.readyState !== 'loading') {
    init();
  } else {
    document.addEventListener('DOMContentLoaded', init);
  }
})();
/******/ })()
;
//# sourceMappingURL=uhp-frontend.js.map