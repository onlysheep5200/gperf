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
 if(!isMapLoaded)
 {
     alert('The map is still loading, please wait for a while');
     return ;
 }
 if(map == null){
  //var initCenter = new google.maps.LatLng(39.915,116.408);
  /*var option = {
    center : initCenter,
    zoom : 2,
    mapTypeId : google.maps.MapTypeId.ROADMAP,

  };*/
  var dom = $('#mapflow .rc').get(0);
  //map = new google.maps.Map(dom,option);}
  map = new Microsoft.Maps.Map(dom,{credentials:'Atkn0qRO0ACrOaPFic7U2u3XSmfEw0o7USgLkfnoAjgrT1PGXdg1oefz40Sl2Eyr',width:$(dom).width(),height:$(dom).height(),enableSearchLogo : false,showDashboard : false});
}
    map.entities.clear();
    markers = [];
    $('#mapflow li.leaf a').removeClass('current');
  $("tr.detail input[name='pid'][checked='checked']").each(function(){
      console.log($(this).val());
      var name = $(this).parent().find('.checkboxspan').text();    
      var pos = $(this).attr('data-position').split(',');
      if(pos.length == 2)
      {
          var pos = new Microsoft.Maps.Location(parseFloat(pos[0]),parseFloat(pos[1]));
          var len = map.entities.getLength();
          var pushpin = new Microsoft.Maps.Pushpin(pos);
          map.entities.push(pushpin);
          markers[name] = len;
      }
      var sideitem = $('#mapflow li.leaf a[data-pid="'+$(this).val()+'"]');
      if(sideitem.length > 0)
      {
          sideitem.addClass('current');
      }
    });
  $("#fullcontent").show();
  $("#mapflow").show();
}
