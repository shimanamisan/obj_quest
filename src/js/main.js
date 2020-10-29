const $ = require("jquery");

$(function(){
    let $history = $('.u-js-history');
    console.log($history)
    setTimeout(()=>{
        $history.each(function(i){
            $(this).delay(200 * i).animate({opacity:1}, 500)
        })
    }, 1000)

})
