define([
    "jquery",
    "logger",
    "dygraph",
    "jquery/ui"
], function($, logger, Dygraph) {
    "use strict";
    logger.log('inchoo.js is loaded!!');
    logger.log(logger);
    logger.log(Dygraph);
    var collection = [];
    $.each(prices, function(key, value) {
        collection.push([new Date(key), value]);
    });
    logger.log(prices);
    logger.log(collection);
    var g = new Dygraph(
        // containing div
        document.getElementById("graphdiv"), collection,
        //[
        //    [new Date("2008-05-07"), 10],
        //    [new Date("2008-05-08"), 20],
        //    [new Date("2008-05-09"), 50],
        //    [new Date("2008-05-10"), 70]
        //],
        {
            //rollPeriod: 15,
            //fractions: true,
            //errorBars: true,
            //showRoller: true,
            labels: [ "x", "A" ]
        }
    );
    //creating jquery widget
    $.widget('inchoo.js', {
        _create: function() {
            //options which you can pass from js.phtml file in json format
            logger.log(this.options);

            //access to element p#test
            logger.log(this.element);

            //for exmple, you can create some click event or something else
            //this.element.on('click', function(e){
            //    logger.log("You click on element: " + e.target);
            //});
        }
    });
    return $.inchoo.js;
});