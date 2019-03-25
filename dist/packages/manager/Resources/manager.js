$(document).ready(function(){

    $('.tabButtons li').removeClass('current').first().addClass('current');
    $('.tabs .tab').removeClass('current').first().addClass('current');

    $('.tabButtons li').on('click',function(){
        var clickedIndex = $(this).index();
        $('.tabButtons li').removeClass('current').eq(clickedIndex).addClass('current');
        $('.tabs .tab').removeClass('current').eq(clickedIndex).addClass('current');
    });
});