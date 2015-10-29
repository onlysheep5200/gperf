$(document).ready(
function()
{
  $(".tabs li").mouseover(
  function()
  {
    $(this).parent().find("li").removeClass("current");
    $(this).addClass("current");
    $(this).parent().parent().parent().find(".bodycontent").hide();
   $(this).parent().parent().parent().find(".bodycontent" + $(this).attr("tabindex")).show();
  }
  );
  $("#prevbtn").click(
  function()
  {
    if($("#imagelist ul").css("left").replace("px","") < (-134 * ($("#imagelist ul li").length - 8)))
    {
      $("#imagelist ul").animate({left:'0px'});
    }
    else
    {
     $("#imagelist ul").animate({left:'-=134px'});
    }
  }
  );
  $("#nextbtn").click(
  function()
  {
    if($("#imagelist ul").css("left").replace("px","") >= 0)
    {
     $("#imagelist ul").animate({left:"-" + 134 * ($("#imagelist ul li").length - 7) + "px"});
    }
    else
    {
     $("#imagelist ul").animate({left:'+=134px'});
    }
  }
  );
  setInterval(function(){$("#prevbtn").click();},5000);
 $("#bannermenus li").mouseover(
  function()
  {
	current = $(this);
    var num= $(this).attr("tabindex");
    $('#bannerlist>ul>li').each(function(i){
    i==num ? $(this).stop().css({'display': 'block', 'zIndex':2}).fadeTo("slow", 1) : $(this).stop().fadeTo("slow", 0, function (){$(this).css({'display': 'none', 'zIndex':1});});
    });
     $('#bannermenus ul li').attr('class','').eq(num).attr('class','current');  
  }
  );
  $(current).mouseover();
  bannertimeset = setInterval("scrollimg()",5000);
  $("#bannercontent").hover(
  function(){clearTimeout(bannertimeset);},
  function(){bannertimeset = setInterval("scrollimg()",5000);}
  );
}
);
var bannertimeset;
var current = $("#bannermenus li:first");
function scrollimg()
{
  current = $(current).next();
  if($(current).length <= 0)
  current = $("#bannermenus li:first");
  var num= $(current).attr("tabindex");
    $('#bannerlist>ul>li').each(function(i){
    i==num ? $(this).stop().css({'display': 'block', 'zIndex':2}).fadeTo("slow", 1) : $(this).stop().fadeTo("slow", 0, function (){$(this).css({'display': 'none', 'zIndex':1});});
    });
     $('#bannermenus ul li').attr('class','').eq(num).attr('class','current');
}
