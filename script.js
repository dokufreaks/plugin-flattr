/**
 * Javascript functionality for the flattr plugin
 */

(function() {
    var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];
    
    s.type = 'text/javascript';
    s.async = true;
    if (location.protocol == 'https:') {
        s.src = 'https://api.flattr.com/js/0.5.0/load.js?mode=auto';
    } else {
        s.src = 'http://api.flattr.com/js/0.5.0/load.js?mode=auto';
    }
    
    t.parentNode.insertBefore(s, t);
})();
