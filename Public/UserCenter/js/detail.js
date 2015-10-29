$(document).ready(function(e){
        
        var innerWidth = $('#taskdetail').width()-20*2;
        var lcWidth = $('.lc').width();
        var rcWidth = innerWidth - lcWidth -20;
        $(".rc").css("width",rcWidth);
        $('.rc img').attr('width',rcWidth+'px');
        });
