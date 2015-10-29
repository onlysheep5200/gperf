$(document).ready(
function()
{
 $("#result dt .span").click(
 function()
 {
   if($(this).parent().find(".checkbox").attr("checked"))
   {
    $(this).parent().find(".checkbox").attr("checked",false);
    $(this).parent().parent().parent().removeClass("current");
   }
   else
   {
    $(this).parent().find(".checkbox").attr("checked","checked");
    $(this).parent().parent().parent().addClass("current");
   }
 });
 $("#result li").each(
 function()
 {
  if($(this).find("dt").find(".checkbox").attr("checked"))
  {
    $(this).addClass("current");
  }
  else
  {
   $(this).removeClass("current");
  }
 });
 $("#result li dt .zoom").click(
 function()
 {
   $("#zoomflow").show();
   $("#fullcontent").show();
   $("#zoomflow .inner").html($(this).parent().parent().parent().find(".none").html());
   $("#zoomflow dt .zooms").click(function(){$("#zoomflow").hide();$("#fullcontent").hide();});
 }
 );
});