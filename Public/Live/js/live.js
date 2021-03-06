﻿$(document).ready(
function()
{

  Array.prototype.contains = function(x)
  {
      for (var i in this)
      {
          if(this[i] == x)
              return true;
      }
      return false;
  }
  Array.prototype.indexOf = function(val)
  {
      for (var x in this)
      {
          if(this[x] == val)
              return x;
      }
      return -1;
  }
  var h = $(window).height();
  $("#mapcontent").css("height",h);
  $(window).resize(function() { 
  h = $(window).height();
  $("#mapcontent").css("height",h);
  });
  if($("#testing li").length > 5)
  {
  scrollnum = $("#testing li").length;
  $("#testing ul").append($("#testing ul").html());
  bannertimeset = setInterval("scrollimg()",3000);
  $("#testing").hover(
  function(){clearTimeout(bannertimeset);},
  function(){bannertimeset = setInterval("scrollimg()",3000);}
  );
  require.config({
      paths: {
		echarts : echart_dir+'echarts',
       	'echarts/chart/map': echart_dir + 'echarts-map',
        'echarts/chart/bar' : echart_dir+'echarts',
      }
  });
  function duplicate(obj){
	  var newObj = {}
	  for (var x in obj)
	  {
		  newObj[x] = obj[x]
	  }
      return newObj
  };
require(
	['echarts','echarts/chart/map','echarts/chart/bar'],
	function(ec){
		var ecConfig = require('echarts/config');
        console.log(ecConfig);
		function eConsole(param) {
		    console.log(param);
		}
		
		var chart = ec.init(document.getElementById('mapcontent'));
		var barChart = ec.init($('.reportform').get(0));
        chart.showLoading({
            text : 'Data is loading now.'
        })
		barChart.showLoading({
			text : 'Data is loading now.'
		})
		var sortedList = [{"link": "www.google.cn", "bandwith": 214.0}, {"link": "gmail.google.com", "bandwith": 24.899999999999999}, {"link": "www.java.com", "bandwith": 21.300000000000001    }, {"link": "www.google.co.jp", "bandwith": 17.300000000000001}, {"link": "www.google.com.ar", "bandwith": 17.300000000000001}, {"link": "www.wikimedia.org", "bandw    ith": 16.800000000000001}, {"link": "www.apple.com", "bandwith": 16.800000000000001}, {"link": "www.google.com.co", "bandwith": 15.9}, {"link": "www.google.it", "ba    ndwith": 15.699999999999999}, {"link": "www.google.com.hk", "bandwith": 15.5}, {"link": "www.google.com.mx", "bandwith": 15.4}, {"link": "www.google.de", "bandwith"    : 15.300000000000001}, {"link": "www.google.at", "bandwith": 15.199999999999999}, {"link": "www.google.nl", "bandwith": 15.0}, {"link": "www.google.co.ve", "bandwit    h": 14.4}, {"link": "www.google.cl", "bandwith": 14.1}, {"link": "www.google.com", "bandwith": 14.0}, {"link": "www.google.fr", "bandwith": 13.9}, {"link": "www.goo    gle.com.tw", "bandwith": 13.300000000000001}, {"link": "www.sohu.com", "bandwith": 9.3800000000000008}, {"link": "www.enorth.com.cn", "bandwith": 3.4700000000000002}];
		var optionExp = {
			backgroundColor : 'black',
			legend: {
			        orient: 'horizontal',
			        x:'center',
			        data:['Ping', 'Traceroute', 'DNS','Bandwidth'],
			        selectedMode: 'single',
			        selected:{
			            'Traceroute' : false,
			            'DNS' : false,
						'Bandwidth' : false
			        },
			        textStyle : {
			            color: '#fff'
			        }
			    },
			series : [
			{
				name : 'All Probe',
				type : 'map',
				mapType : 'world',
				roam : false,
				itemStyle:{
				   normal:{
				       borderColor:'white',
				       borderWidth:0.5,
				       areaStyle:{
				         	color: '#1b1b1b'
				       }
				   }
				},
			    
				markPoint : {
				   smooth:true,
				    symbol : 'circle', 
                    symbolSize : 1,
				   	itemStyle : {
				        normal: {
				          borderWidth:1,
                          color : '#fff',
                          borderColor:'rgba(30,144,255,0.5)'
				        },
					},
                    data : [{name : 'shanghai',geoCoord : [114.118,22.372]}],
				},
                data : [],
				
				}
			,
			{
				name : 'Ping',
				type : 'map',
				mapType : 'world',
				roam : false,
				itemStyle:{
				   normal:{
				       borderColor:'white',
				       borderWidth:0.5,
				       areaStyle:{
				         	color: '#1b1b1b'
				       }
				   }
				},
				markPoint : {
				   smooth:true,
				   effect : {
				     	show: true,
				        scaleSize: 3,
				     	period: 30,
				        color: '#fff',
				        shadowBlur: 10
				    },
				   	itemStyle : {
				        normal: {
				          borderWidth:1,
				          lineStyle: {
				             type: 'solid',
				             shadowBlur: 10
				           }
				        },
					},
					
                    data : [],
				},
                data : []
            }
				,{
				name : 'TraceRoute',
				type : 'map',
				mapType : 'world',
				roam : false,
				itemStyle:{
				   normal:{
				       borderColor:'white',
				       borderWidth:0.5,
				       areaStyle:{
				         	color: '#1b1b1b'
				       }
				   }
				},
				data : []
			},{
				name : 'DNS',
				type : 'map',
				mapType : 'world',
				roam : false,
				itemStyle:{
				   normal:{
				       borderColor:'white',
				       borderWidth:0.5,
				       areaStyle:{
				         	color: '#1b1b1b'
				       }
				   }
				},
				data : []
			},{
				name : 'Bandwidth',
				type : 'map',
				mapType : 'world',
				roam : false,
				itemStyle:{
				   normal:{
				       borderColor:'white',
				       borderWidth:0.5,
				       areaStyle:{
				         	color: '#1b1b1b'
				       }
				   }
				},
				data : []
			}]
		}
        var barOption = {
            tooltip: {
                trigger: 'axis'
            },
            legend: {
                data: ['Bandwith'],
                textStyle: {
                    color: 'white'
                },
            },
            calculable: true,
            xAxis: [{
                type: 'category',
                axisLabel: {
                    rotate : -45,
                    textStyle: {
                        color: 'rgb(255,255,255)',
                        fontSize: '5px',
                    }
                },
				data : (function(){
					var d = [];
					for (var i = 0;i<5;i++)
					{
						d.push(sortedList[i].link);
					}
					return d;
				})(),
            }],
            yAxis: [{
                type: 'value',
                axisLabel: {
                    textStyle: {
                        color: 'rgb(255,255,255)',
                    }
                }
            }],
            series: [{
                name: 'Bandwidth',
                type: 'bar',
				data : (function(){
					var d = [];
					for(var i = 0;i<5;i++)
					{
						d.push(sortedList[i].bandwith);
					}
					return d;
				})()
            },
            ]
        };
		barChart.setOption(barOption);
		barChart.hideLoading();
/*		var option = {
		    timeline:{
		           data:[
				   '13:00','13:05','13:10','13:15','13:20','currnet'
		           ],
                   textStyle : {
                       color : '#fff'
                   },
				   label : {
				               formatter : function(s) {
				                   return s;
				               }
				           },
		           autoPlay : true,
				   palyInterval : 1000
		       },
			   options :(function(){
				   var datas = []
                   datas.push(optionExp)
				   for (var i = 0;i<6;i++)
				   {
                       datas.push({series : optionExp.series})
				   }
                   console.log(datas)
				   return datas
			   })(),
		}*/
        $.getJSON(location_dir,function(data){
            var placelist = data
            console.log(placelist);
            for (var x in optionExp.series)
            {
                if(optionExp.series[x].name == "All Probe")
				{
                    optionExp.series[x].markPoint.data = placelist;
					optionExp.series[x].geoCoord = placelist;
				}
                else if (optionExp.series[x].name == "Ping")
                    optionExp.series[x].markPoint.data = [placelist[1],placelist[0]];
            }
            var hasLineIndex = [];
			function onPointCLick(parm)
			{
                console.log(parm);
				if(parm.dataIndex == 1 || parm.dataIndex == 0)
				{
                    console.log(parm);
					var index = parm.dataIndex;
					var opt = chart.getOption();
                    var markline = opt.series[1].markLine;
					if(markline && hasLineIndex.contains(index))
					{
                        var i = hasLineIndex.indexOf(index);
                        hasLineIndex.splice(i,1);
                        chart.delMarkLine(1,placelist[index].name+' > '+placelist[3].name);
                        chart.delMarkLine(1,placelist[index].name+' > '+placelist[4].name);
                        chart.delMarkLine(1,placelist[index].name+' > '+placelist[5].name);
                        var tmp = chart.getOption();
                        chart.clear();
                        chart.setOption(tmp);
                        console.log('not markline')
                        console.log(chart.getOption());
					}
					else
					{
						var markLine = {
						                smooth:true,
						                effect : {
						                    show: true,
						                    scaleSize: 1,
						                    period: 30,
						                    color: '#fff',
						                    shadowBlur: 10
						                },
						                itemStyle : {
						                    normal: {
						                        borderWidth:1,
						                        lineStyle: {
						                            type: 'solid',
						                            shadowBlur: 10
						                        }
						                    }
						                },
										data:[
                                            [placelist[index],placelist[3]],
                                            [placelist[index],placelist[4]],
                                            [placelist[index],placelist[5]],
                                         ],
									};
						chart.addMarkLine(1,markLine)
                        console.log(chart.getOption())
                        hasLineIndex.push(index);
					}
					
				}
			}
			chart.on(ecConfig.EVENT.CLICK, onPointCLick);
            chart.on('mapSelected',function(param){
                console.log('map selected');    
            })
            chart.setOption(optionExp)
            chart.hideLoading()
			
        })
		
	});
  
  }
});
var scrollnum = 0;
var bannertimeset;
function scrollimg()
{
   if($("#testing ul").css("top").replace("px","") < (-30 * ($("#testing li").length - 6)))
    {
      //$("#testing ul").animate({top:-30 * scrollnum + 150 + 'px'});
      $("#testing ul").css("top",-30 * scrollnum + 150 + 'px');
    }

     $("#testing ul").animate({top:'-=30px'});
}
