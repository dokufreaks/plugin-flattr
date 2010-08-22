/**
 * Javascript functionality for the flattr plugin
 */

(function() {
    var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];
    
    s.type = 'text/javascript';
    s.async = true;
    s.src = 'http://api.flattr.com/js/0.5.0/load.js';
    
    t.parentNode.insertBefore(s, t);
})();

addInitEvent(function() {
    FlattrLoader.setup();
});