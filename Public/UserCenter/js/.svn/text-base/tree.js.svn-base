$(document).ready(
function()
{

 $("#tree li a").click(
 function()
 {
  $("#tree li a").removeClass("current");
  $(this).addClass("current");
 }); 
 $("#searchresult .table .checkboxspan").click(
 function()
 {
   if($(this).parent().find(".checkbox").attr("checked"))
   {
    $(this).parent().find(".checkbox").attr("checked",false);
    $(this).removeClass("checkboxspan_current");
   }
   else
   {
    $(this).parent().find(".checkbox").attr("checked","checked");
    $(this).addClass("checkboxspan_current");
   }
 });
  $("#searchresult .table .checkboxspan").each(
  function()
  {
   if($(this).parent().find(".checkbox").attr("checked"))
   {
     $(this).addClass("checkboxspan_current");
   }
   else
   {
     $(this).removeClass("checkboxspan_current");
   }
  });
  $("#searchresult .table .span").click(
  function()
  {
    var i = $(this).parent().parent();
    i = $(i).next();
    if($(this).attr("class").indexOf("spancurrent") >= 0)
    {
     $(this).removeClass("spancurrent");
     
     for(var j=0;j>=0;j++)
     {

       if($(i).attr("class").indexOf("detail") >= 0)
       {
         $(i).hide();
         i = $(i).next();
       }
       else
       {
         j = -1;
       }
     }
    }
    else
    {
     $(this).addClass("spancurrent");
     for(var j=0;j>=0;j++)
     {
       if($(i).attr("class").indexOf("detail") >= 0)
       {
         $(i).show();
         i = $(i).next();
       }
       else
       {
         j = -1;
       }
     }
    }
  });
});
function closemapcontent()
{
  $("#fullcontent").hide();
  $("#mapflow").hide();
}
function showmapcontent()
{
  $("#fullcontent").show();
  $("#mapflow").show();
}