try {
    /** Важно!!! window.$ не работает для jquery, используйте global. **/
    global.JQuery = global.$;
    window.Tether = require('tether');
    window.Popper = require('popper.js');
} catch (e) {}



