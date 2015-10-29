(function(dir) {
    var echart_dir = dir + 'echarts';
    require.config({
        paths: {
            'echarts': echart_dir,
            'echarts/chart/bar': echart_dir,
            'echarts/chart/line': echart_dir,
            'echarts/chart/pie': echart_dir,
            'echarts/chart/radar': echart_dir,
            'echarts/chart/chord': echart_dir,
            'echarts/chart/force': echart_dir,
        }
    });
    $.drawSinglePic = function(obj) {
        var chart;
        require(
            ['echarts', 'echarts/chart/bar', 'echarts/chart/pie', 'echarts/chart/radar', 'echarts/chart/chord', 'echarts/chart/force'], function(ec) {
                //option is the config for echart
                var option = obj.option;
                //dom is jquery object
                var dom = obj.dom.get(0);
                chart = ec.init(dom);
                chart.showLoading({
                    text:'Report data is loading now...', 
					effect : 'spin'   
                })
                chart.setOption(option);
                chart.hideLoading();
            })
        return chart;
    };
    $.createPingReportOption = function(title, subtitle) {
        var option = {
            tooltip: {
                trigger: 'axis'
            },
            legend: {
                data: ['Delay', 'Loss'],
                textStyle: {
                    color: 'white'
                },
            },
            backgroundColor: 'rgba(0,0,0,0.9)',
            calculable: true,
            xAxis: [{
                type: 'category',
                axisLabel: {
                    rotate : -45,
                    textStyle: {
                        color: 'rgb(255,255,255)',
                        fontSize: '5px',
                    }
                }
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
                name: 'Delay',
                type: 'bar',
                //        markPoint : {
                //          data : [
                //            {type : 'max', name: 'Max Value'},
                //          {type : 'min', name: 'Min Value'}
                //    ]
                // },
                markLine: {
                    data: [{
                        type: 'average',
                        name: 'Average Delay Time'
                    }]
                }
            }, {
                name: 'Loss',
                type: 'bar',
                markLine: {
                    data: [{
                        type: 'average',
                        name: 'Average Loss Percentage'
                    }]
                }
            }]
        };
        return option;
    };
    $.createDnsOption = function(data) {
        var options = {};
		var nodesAndLinks = {}
        var option = {
            backgroundColor : 'rgba(0,0,0,0.9)',
            tooltip: {
                trigger: 'item',
                formatter: '{a} : {b}'
            },
            legend: {
                x: 'left',
                selectedMode : 'single',
                data: [],
                textStyle : {
                    color : 'white'
                }
            },
            series: [],
        };
        for (var dest in data.dests) {
            var nodes = [];
            nodes.push({
                category: 0,
                name: data.dests[dest],
                depth : 1,
				value : 15,
                draggable: true,
                itemStyle: {
                    normal: {
                        label: {
                            textStyle: {
                                color: 'white'
                            }
                        }
                    }
                }
            });
            var addrs = data[data.dests[dest]];
            for (var x in addrs) {
                nodes.push({
                    category: 1,
                    name: addrs[x].ip,
                    value: 10,
					depth: 2,
                });
            };
           // nodes.push({
             //   category: 2,
               // name: data.probe,
               // value: 15,
               // draggable: true,
           // });
            var links = [];
            for (var x = 1; x < nodes.length; x++) {
                links.push({
                    source: x,
                    target: 0,
                    weight: 10,
                });
            /*    links.push({
                    source: nodes.length - 1,
                    target: x,
                    weight: 1,
                })*/
            }
			nodesAndLinks[data.dests[dest]] = {nodes : nodes,links : links}
		}
		
            //options[data.dests[dest]] = option;
        	for (var x in nodesAndLinks)
			{
				var obj = {
                    type: 'force',
                    name: x,
                    gravity: 0.5,
                    categories: [{
                        name: 'dest'
                    }, {
                        name: 'IP Address',
                    },/* {
                        name: 'probe'
                    }*/],
                    itemStyle: {
                        normal: {
                            label: {
                                show: false,
                                textStyle: {
                                    color: '#333'
                                }
                            },
                            nodeStyle: {
                                brushType: 'both',
                                strokeColor: 'rgba(255,215,0,0.4)',
                                lineWidth: 1
                            }
                        },
                        emphasis: {
                            label: {
                                show: false
                                // textStyle: null      // 默认使用全局文本样式，详见TEXTSTYLE
                            },
                            nodeStyle: {
                                //r: 30
                            },
                            linkStyle: {}
                        }
                    },
                    useWorker: false,
                    minRadius: 15,
                    maxRadius: 25,
                    gravity: 1.1,
                    scaling: 1.2,
                    draggable: false,
                    linkSymbol: 'arrow',
                    steps: 10,
                    coolDown: 0.9,
                    nodes: nodesAndLinks[x].nodes,
                    links: nodesAndLinks[x].links,
                }
				option.series.push(obj)
				option.legend.data.push(x)
			}
        return option;
    };
    $.createTracertOption = function(obj) {
        var options = [];
        var option = {
			backgroundColor : 'rgba(0,0,0,0.9)',
            calculable: true,
            tooltip :
            {
                trigger : 'axis',
            },
            legend: {
                data: [],
                textStyle: {
                    color: 'white'
                },
            },
            xAxis: [{
                type: 'category', 
				data :[],
				axisLabel :{
                	textStyle: {
                    	color: 'white',
                	}
				}
            }],
            yAxis: [{
                type: 'value',
                axisLabel: {
                    formatter: '{value} ms',
					textStyle :
					{
						color : 'white',
					}
                },
            }],
			series : []
            // series: [ {
   //              name: 'ttl',
   //              type: 'line',
   //              yAxisIndex: 0,
   //          }]
        };
		var xLength = 0;
		for (var x in obj.dests)
		{
			option.legend.data.push(obj.dests[x]);
			var destData = obj[obj.dests[x]];
			var nums = destData.length;
			if(nums > xLength)
			{
				xLength = nums;
			}
			var serie = {
				name : obj.dests[x],
				type : 'line',
				yAxisIndex : 0,
				data : $.map(destData,function(e,i){
                    if(e.ip == null)
                        return;
					var result = {
						value : e.time,
						tooltip : {
							trigger : 'item',
							formatter : function(s){
								return 'IP/Address:'+e.ip+"<br/>"+'TTL: '+(i+1)+"<br/>"+"Delay: "+e.time;
							}
						}
					}
					return result;
				})
			};
			option.series.push(serie);
		}
		for (var i = 0;i<xLength;i++)
		{
			option.xAxis[0].data.push(i+1);
		} 
        // for (var x in obj.dests) {
        //     var hips = obj[obj.dests[x]];
        //     var option = {
        //         tooltip: {
        //             trigger: 'axis',
        //         },
        //         title:
        //         {
        //             x: 'right',
        //             y: 'bottom',
        //             text : obj.dests[x]
        //         },
        //
        //         calculable: true,
        //         legend: {
        //             data: ['time','ttl']
        //         },
        //         xAxis: [{
        //             type: 'category',
        //         }],
        //         yAxis: [{
        //             type: 'value',
        //             name: 'time',
        //             axisLabel: {
        //                 formatter: '{value} ms'
        //             },
        //         }],
        //         series: [{
        //             name: 'time',
        //             type: 'bar',
        //         },  {
        //             name: 'ttl',
        //             type: 'line',
        //             yAxisIndex: 0,
        //         }]
        //     };
        //     option.xAxis[0].data = $.map(hips,function(e,i){
        //         return e.ip;
        //     });
        //     option.series[0].data = $.map(hips,function(e,i){
        //         return e.time;
        //     })
        //     option.series[1].data = $.map(hips,function(e,i){
        //         return e.time;
        //     })
        //      options.push(option);
        // }
        return option;
    };
})(echart_dir)
