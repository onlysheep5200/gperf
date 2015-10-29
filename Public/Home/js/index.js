$(document).ready(
function()
{
//  $("#bannercontent .prev").click(
//  function()
//  {
//    current = $(current).prev();
//  if($(current).length <= 0)
//  current = $("#bannermenus li:last");
//  var num= $(current).attr("tabindex");
//  $("#bannercontent .itemlist").animate({left:"-" + 1100 * num + "px"});
//  $('#bannermenus ul li').attr('class','').eq(num).attr('class','current');
//  $("#bannertitle").html($(current).find(".none").html()); 
//  }
//  );
//  $("#bannercontent .next").click(
//  function()
//  {
//    scrollimg();
//  }
//  );
//  setInterval(function(){$("#prevbtn").click();},5000);

    window.chart = null
	require.config({
   		paths: {
			echarts : echart_dir + 'echarts',
     		'echarts/chart/map': echart_dir + 'echarts-map'
    	}
	});
	require(
		['echarts','echarts/chart/map'],
		function(ec){
			chart = ec.init(document.getElementById('overall_map'));
            chart.showLoading({
                text : 'data is loading now'    
            })
			var option = {
				backgroundColor : 'white',
				dataRange: {
				        min: 0,
				        max: 500,
				        text:['High','Low'],
				        realtime: false,
				        calculable : true,
				        color: ['orangered','yellow','lightskyblue']
				 },
                tooltip : 
                {
                    trigger : 'item',
                    formatter : function(a){
                        var cname = a[1]
                        var pnum = a[2]
                        return cname + ':' + pnum
                    }
                },
				series : [
				{
					name : 'All Probe',
					type : 'map',
					mapType : 'world',
                    selectedMode : true,
					roam : false,
					itemStyle:{
					   emphasis:{label:{show:true}}
					},
                    mapLocation : {
                        y:10,
                    },
			        data : []
					
					}
				]
			};
			
            $.getJSON('Public/static/probenum.json',function(placelist){
               option.series[0].data = placelist 
		       chart.setOption(option);
               chart.on('click',function(param){
               $('input[name="search_args"]').val(param.name);
               $("#main_search_btn").trigger('click');
               })
               chart.hideLoading()
            }) 
		}
	);
 $("#bannermenus span").mouseover(
  function()
  {
	current = $(this);
    var num= $(this).attr("tabindex");
    $("#bannercontent .itemlist").animate({left:"-" + 570 * num + "px"});
    $('#bannermenus span').attr('class','').eq(num).attr('class','current'); 
    $("#bannertitle").html($(current).find(".none").html()); 
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
var current = $("#bannermenus span:first");
function scrollimg()
{
  current = $(current).next();
  if($(current).length <= 0)
  current = $("#bannermenus span:first");
  var num= $(current).attr("tabindex");
  $("#bannercontent .itemlist").animate({left:"-" + 570 * num + "px"});
  $('#bannermenus span').attr('class','').eq(num).attr('class','current');
  $("#bannertitle").html($(current).find(".none").html()); 
}
