var fs = window.fs||{};

(function(){
    fs.trigger=function(event, ele){
        $(ele||window).trigger(event);
    };

    fs.on=function(event, callback, ele){
        $(ele||window).on(event, callback);
    };
})();