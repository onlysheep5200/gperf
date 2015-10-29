﻿function getProbeTaskStatus(type)
{
    var pts = [];
     $('li.'+type).each(function(){
          var pid = $(this).find('input[name="pid"]').val();
          var tid = $(this).find('input[name="tid"]').val();
          pts.push({tid:tid,pid:pid});
        });
     console.log(pts);
     for(var x in pts)
     {
         (function(arg,type){
         $.getJSON(check_status_addr,arg,function(data){
             console.log(data);
            if(data == "illegal task")
            {
                alert(data);
                window.location.href=index_addr;
            }
            else if (data == "no task exists")
            {
                alert(data);
                window.location.href=index_addr;
            }
            else if (data == 'queueing')
                return;
            $("li."+type).each(function(){
                if($(this).find('input[name="tid"][value="'+data.tid+'"]').length!=0  && $(this).find('input[name="pid"][value="'+data.pid+'"]').length != 0){
                $(this).find(".loading").remove();
                var dd  = $(this).find('dd');
                dd.find('.src').show();
                dd.find(".starttime").show().html('<label class="label">Start Time: </label>'+data.starttime);
                dd.find(".endtime").show().html('<label class="label">End Time: </label>'+data.endtime);
                dd.find(".status").show().html('<label class="label">Status:</label>'+data.status);
                if(type == 'ping' || type == 'dns' || type == 'tracert' || type == 'bandwidth')
                {
                     $(this).find(".zoom").trigger('click');    
                }
                }
            });
        })})(pts[x],type);
     }
}
$(document).ready(
function()
{
var pingDatas = {};
var pingDests = [];
var dnsOptions = {};
var tracertOptions = {};
var bandwidthOptions = {};
var tabs = $('.result_tabs ul li');
tabs.eq(0).addClass('current');
var  cur_type = $(".result_tabs ul").find('li.current').find('a').text();
function initDisplay()
{
    console.log(cur_type);
   $('#result-list li').not("."+cur_type).hide(); 
   $('#result-list li').filter('.'+cur_type).show();
}
tabs.find('a').click(function(e){
    e.preventDefault();
    cur_type = $(this).text();
    tabs.filter('.current').attr('class','');
    $(this).parent().addClass('current');
    initDisplay();
    getProbeTaskStatus(cur_type);

})


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
     console.log($(this).parent().parent().find('.status').text());
     if($(this).parent().parent().find('.status').text()!="Status:done")
     {
         alert('Your task is still performing.Please wait until it has been completed');
         return;
     }
     var pid = $(this).parent().parent().find('input[name="pid"]').val();
     var tid = $(this).parent().parent().find('input[name="tid"]').val();
     var item1 = $(this).parent().parent().parent();
     var item = $(this).parent().parent();
     $.getJSON(report_dir,{pid:pid,tid:tid},function(data)
     {
        console.log(data); 
         //$("#zoomflow").show();
         //$("#fullcontent").show();
         //$("#zoomflow .inner").html(item.find(".none").html());
         //$("#zoomflow dt .zooms").click(function(){$("#zoomflow").hide();$("#fullcontent").hide();});
		 item.find('#chartarea').height(165);
         if(data.type == 'ping')
         {
            if(!(data.probe in pingDatas))
            {
				for (var x in data.delays)
				{
					if(data.delays[x] == null)
					{
						data.delays[x] = -1;
					}
				}
                var option = $.createPingReportOption(data.title,data.subtitle); 
                option.xAxis[0].data = data.dests;
                option.series[0].data = data.delays;
                option.series[1].data = data.losses;
                var pdata = {losses : data.losses,delays:data.delays,option:option};
                //eval("pingDatas."+data.probe+"=pdata");
                pingDatas[data.probe] = pdata;
                if(pingDests.length != 0)
                {
                    pingDests.concat(data.dests);
                }
                console.log(pingDatas);
                console.log(pingDests);
                item.find('#chartarea').height(165);
                var args = {option : option,dom : item.find("#chartarea")};
                var chart = $.drawSinglePic(args);
            }
            item.find("#chartarea").attr('data-value',data.probe).attr('data-type','ping').toggle();
         }
         else if (data.type == 'dns')
         {
             // if($(".dns"+data['probe']).length != 0)
 //             {
 //                 $(".dns"+data['probe']).show();
 //                 $("fullcontent").show();
 //                 return;
 //             }
            //var options;
			// var option;
			//             var none = item1.find('.none');
			//             var zoom = $("#zoomflow.raw").clone();
			//             zoom.attr('class','dns'+data['probe']);
			//             $('#zoomflow').after(zoom);
			//             zoom = $(".dns"+data['probe']);
			//             var inner = zoom.find('.inner');
			//             inner.html('');
			//             inner.html(none.html());
			//             if(!(data.probe in dnsOptions)){
			//                // options = $.createDnsOption(data);
			//    	option = $.createDnsOption(data)
			//                 dnsOptions[data.probe] = option;
			//                 }
			//             //options = dnsOptions[data.probe];
			// option = dnsOptions[data.probe]
			//             console.log(option)
			//             var div = $('<div></div>');
			//             div.width(800);
			//             div.height(400);
			// var tmp = div.clone();
			// tmp.attr("id",'tmp');
			// inner.find('dd').append(tmp);
			// tmp = inner.find("#tmp");
			// var chart = $.drawSinglePic({option:option,dom:tmp});
			// tmp.attr('id','');
			//             $(".dns"+data['probe']+" dt .zooms").click(function(){zoom.hide();$("#fullcontent").hide();});
            //$("#zoomflow").show();
            // zoom.show();
 //            $("#fullcontent").show();
 			if(!(data.probe in dnsOptions))
 		   {
     		  	var option = $.createDnsOption(data); 
     		   	dnsOptions[data.probe] = option
     			item.find('#chartarea').height(165);
     		   	var args = {option : option,dom : item.find("#chartarea")};
     		   	var chart = $.drawSinglePic(args);
 		   }
 		  	item.find("#chartarea").attr('data-value',data.probe).attr('data-type','dns').toggle();
            
         }
         else if (data.type == 'tracert')
         {
             var none = item1.find('.none'); 
             var inner = $('#zoomflow .inner');
             inner.html(none.html());
             if(!(data.probe in tracertOptions))
             {
                 var option = $.createTracertOption(data);
				 tracertOptions[data.probe] = option;
                 console.log(option);
                     var chart = $.drawSinglePic({option : option,dom:item.find('#chartarea')});
             }
			item.find("#chartarea").attr('data-value',data.probe).attr('data-type','tracert').toggle();
         }
		 else if (data.type == 'bandwidth')
		 {
             if(!(data.probe in bandwidthOptions))
             {
                 var option = $.createPingReportOption(data.title,data.subtitle); 
				 option.legend.data = ['Bandwidth'];
                 option.xAxis[0].data = data.dests;
				 option.xAxis[0].axisLabel.rotate = 0;
				 option.series = [
				 {
					 name : 'Bandwidth',
					 data : data.bandwidths,
					 type : 'bar',
				 },];
                 bandwidthOptions[data.probe] = option;
                 item.find('#chartarea').height(165);
                 var args = {option : option,dom : item.find("#chartarea")};
                 var chart = $.drawSinglePic(args);
             }
             item.find("#chartarea").attr('data-value',data.probe).attr('data-type','bandwidth').toggle();
		 }
        
     });
 }
 );
    $(".ping #chartarea").dblclick(function(e)
    {
        console.log('double clicked');
        e.preventDefault();
        var probe = $(this).attr('data-value');
        if(probe != '' && probe)
        {
            console.log(probe);
            var option = pingDatas[probe]['option'];                           
            option.toolbox = {
                show : true,
                feature : 
                {
                   mark : {show:true},
                   dataView : {show:true,readOnly:false},
                   magicType : {show:true,type:['bar','line']},
                   restore : {show:true},
                   saveAsImage : {show:true},
                }
            };
            option.xAxis[0].axisLabel.textStyle.color = 'black';
            option.yAxis[0].axisLabel.textStyle.color = 'black';
            option.legend.textStyle.color = 'black';
            option.backgroundColor = "white";
            var zoomflow = $('#zoomflow');
            var inner = zoomflow.find('.inner');
            inner.find('dt').html($(this).parent().parent().find('dt').html());
            inner.find('.zoom').attr('class','zooms');
            var dd = inner.find('dd');
            dd.html('');
            var div = $("<div></div>").height(400).width(800);
            dd.append(div);
            div = dd.find('div');
            var chart = $.drawSinglePic({option:option,dom:div});
            $("#fullcontent").show();
            $("#zoomflow dt .zooms").click(function(){$("#zoomflow .inner dd").html('');$("#zoomflow").hide();$("#fullcontent").hide();});
            zoomflow.show();
        }
    });
    $(".bandwidth #chartarea").dblclick(function(e)
    {
        console.log('double clicked');
        e.preventDefault();
        var probe = $(this).attr('data-value');
        if(probe != '' && probe)
        {
            console.log(probe);
            var option = bandwidthOptions[probe];                           
            option.toolbox = {
                show : true,
                feature : 
                {
                   mark : {show:true},
                   dataView : {show:true,readOnly:false},
                   magicType : {show:true,type:['bar','line']},
                   restore : {show:true},
                   saveAsImage : {show:true},
                }
            };
            option.xAxis[0].axisLabel.textStyle.color = 'black';
            option.yAxis[0].axisLabel.textStyle.color = 'black';
            option.legend.textStyle.color = 'black';
            option.backgroundColor = "white";
            var zoomflow = $('#zoomflow');
            var inner = zoomflow.find('.inner');
            inner.find('dt').html($(this).parent().parent().find('dt').html());
            inner.find('.zoom').attr('class','zooms');
            var dd = inner.find('dd');
            dd.html('');
            var div = $("<div></div>").height(400).width(800);
            dd.append(div);
            div = dd.find('div');
            var chart = $.drawSinglePic({option:option,dom:div});
            $("#fullcontent").show();
            $("#zoomflow dt .zooms").click(function(){$("#zoomflow .inner dd").html('');$("#zoomflow").hide();$("#fullcontent").hide();});
            zoomflow.show();
        }
    });
    $(".tracert #chartarea").dblclick(function(e)
    {
        console.log('double clicked');
        e.preventDefault();
        var probe = $(this).attr('data-value');
        if(probe != '' && probe)
        {
            console.log(probe);
            var option = tracertOptions[probe];                           
            option.toolbox = {
                show : true,
                feature : 
                {
                   mark : {show:true},
                   dataView : {show:true,readOnly:false},
                   magicType : {show:true,type:['bar','line']},
                   restore : {show:true},
                   saveAsImage : {show:true},
                }
            };
            option.xAxis[0].axisLabel.textStyle.color = 'black';
            option.yAxis[0].axisLabel.textStyle.color = 'black';
            option.legend.textStyle.color = 'black';
            option.backgroundColor = "white";
            var zoomflow = $('#zoomflow');
            var inner = zoomflow.find('.inner');
            inner.find('dt').html($(this).parent().parent().find('dt').html());
            inner.find('.zoom').attr('class','zooms');
            var dd = inner.find('dd');
            dd.html('');
            var div = $("<div></div>").height(400).width(800);
            dd.append(div);
            div = dd.find('div');
            var chart = $.drawSinglePic({option:option,dom:div});
            $("#fullcontent").show();
            $("#zoomflow dt .zooms").click(function(){$("#zoomflow .inner dd").html('');$("#zoomflow").hide();$("#fullcontent").hide();});
            zoomflow.show();
        }
    });
 initDisplay();
});
