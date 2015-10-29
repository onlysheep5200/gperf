$(document).ready(
function()
{
  var h = $(window).height();
  $("#maincontent").css("min-height",h-79-95);
  $("#nav li").hover(
  function(){$(this).addClass("hover");},
  function(){$(this).removeClass("hover");}
  );
  $(window).resize(function() { 
  h = $(window).height();
  $("#maincontent").css("min-height",h-79-95);
  });

  $("#search li").click(
  function()
  {
   $("#search .searchtxt").val($(this).find(".key").html());
   $("#search .none").hide();
  });
  // $("#search .none ul li").click(
  // function()
  // {
  //  $("#search .searchtxt").val($(this).find(".key").html());
  //  // $("#search .none").hide();
  // });
  // $("div.none ul li").click(function(){
  // 	$(".searchtxt").val($(this).html());
  // 	$("div.none").hide();
  // });
 
  // $(window).resize(function(){ 
  //     if($("body").height()>$(window).height())
  //       return;
  //     $('#maincontent').height($(window).height()-$("#footer").outerHeight()-$("#header").outerHeight());
  //     });

  $("#main_search_btn").click(function(e){
  		if($(".searchtxt").val()=="")
  		{
  			e.preventDefault();
  		}
  });

  $(".searchtxt").click(function(){
  		if($(".searchtxt").val() == "")
  		{
  			$("div#search").find("div.none ul").html("");
  			$("div#search").find("div.none").hide();
  		}
  })
  $("#search .searchtxt").keyup(
  function()
  {
   /*ajax写在这儿*/
   if($.trim($("#search .searchtxt").val()) != "")
   {
   	$("#search .none ul").html("");
   	$.getJSON(index_addr,{arg : $.trim($("#search .searchtxt").val())},function(data)
   	{
   		if(data=="none")
   		{
   			$(".none").hide();
   			return;
   		}
   		$.each(data,function(i,item)
   		{
   			var li = $("<li>"+item+"</li>");
   			li.click(function(){
   				$('.searchtxt').val($(this).html());
  	 			$("#search").find('div.none').hide();	
   			});
   			$("#search .none ul").append(li);
   		});
   		$("#search .none").show();
   	});
   }
   else
   {
   	$("#search .none ul").html("");
    $("#search .none").hide();
   }
  });
  
 	$('.menu').hover(function(){
 		$(".menu").each(function(){
            $(this).parent().removeClass('current');    
        });
 		$(this).parent().addClass('current');
 	})

  var searchcurrent;
  $("#search .searchtxt").keydown(function(event){
  event = (event) ? event : ((window.event) ? window.event : ""); //兼容IE和Firefox获得keyBoardEvent对象
  var key = event.keyCode?event.keyCode:event.which;//兼容IE和Firefox获得keyBoardEvent对象的键值 
  //var key = event.keyCode;  
		if (key == 13)
		{
			//执行搜索
			/*回车搜索*/  
			if($('.searchtxt').val()!='')
				$("form").submit();
		}  
		if (key == 38)//向上
		{
			if($(searchcurrent).length <= 0)
			{
				searchcurrent = $("#search li:last");
			}
			else
			{
				searchcurrent = $(searchcurrent).prev();
			}
			$("#search li").removeClass("current");
			$(searchcurrent).addClass("current")
			$("#search .searchtxt").val($(searchcurrent).find(".key").html());
		}
		if (key == 40)//向下
		{
			if($(searchcurrent).length <= 0)
			{
				searchcurrent = $("#search li:first");
			}
			else
			{
				searchcurrent = $(searchcurrent).next();
			}
			$("#search li").removeClass("current");
			$(searchcurrent).addClass("current");
			$("#search .searchtxt").val($(searchcurrent).find(".key").html());
		}
	});  

//search page
	$("#to_configure").click(function(e){
		e.preventDefault();
		var checkboxs = $('input[name="pid"]');
		var hasValue = false;
		var values="";
		$.each(checkboxs,function(i,item){
			if($(this).attr('checked')=='checked')
			{
				values += $(this).val()+' ';
			}
		});
		if(values != "")
		{
			$("input[name='values']").val(values);
			$("form").submit();
		}
	})
});

//configure page
function onCloseClicked()
{
	//$(this).parent().remove();
    var type = $(this).parent().attr('data-value');
    $("div."+type).remove();
    $(this).parent().remove();
    var next_show = $("#label-container").find("label:first").attr('data-value');
    if(next_show)
        $('div.'+next_show).show();

}

function onSrctypeChanged()
{

	if($(this).val()=='1')
	{
		var li = $("#probelist").clone();
		li.addClass("added");
		$(this).parent().after(li);
		li.show();
	}
	else
	{
		$(this).parent().parent().find(".added").remove();
	}
}
$('input[name="srctype"]').change(onSrctypeChanged);
function onLabelClicked()
{
	$(".key.type").removeClass('current_key');
	$(this).addClass('current_key');
	var type = $(this).attr('data-value');
	$('.args').children().hide();
	if((wanted = $('.args').find("div."+type)).length!=0)
	{
		wanted.show();
	}
	else
	{
		$.get(configure_addr,{type : type},function(data){
            var form = $(data);
            form.find('input[name="srctype"]').change(onSrctypeChanged);
			$('.args').append(form);
		});
	}
}
$("#service-selector").change(function(){
	// if($("label.key.type").length>=1)
	// {
	// 	alert("Multi task operation is still developing now");
	// 	return;
	// }
	var text = $("#service-selector").find("option:selected").text();
	var data_value = $("#service-selector").val();
	var labels = $("label.key.type");
	var exists = false;
	$.each(labels,function(i,item){
		if($(item).attr('data-value') == data_value)
			exists=true;
	});
	if(exists)
		return;
	var label = $('<label class="key type" data-value="'+data_value+'"">'+text+'<span class="close">×</span></label>');
	$(".key.type.current_key").removeClass("current_key");
	label.addClass('current_key');
	label.find('span').click(onCloseClicked);
	label.click(onLabelClicked);
	$("div#label-container").append(label);
	label.trigger('click');
});

var serviceNum = 0;
var serviceSubmit = 0;
$('#submitbtn').click(function()
{
	//$("form").submit();
    var services = [];
    $("#label-container").find('label').each(function(){
            services.push($(this).attr('data-value'));
            serviceNum +=1;
        })
    //$("input[name='dest']").val($('#target-selector option').map(function(){return $(this).text()}).get().join(' '));
    $("input[name='dest']").each(function(){
            $(this).val(
                $(this).parent().find("#target-selector option").map(function(){return $(this).text()}).get().join(' ').trimRight()
                )
            console.log($(this).val());
        })
    var token = $("input[name='token']").clone();
    for(var i=0;i<services.length-1;i++)
    {
        $("#"+services[i]+"_form").attr("action",run_addr).attr("method","post").attr("target",services[i]+"_frame").addClass('need').addClass('has_target').append('<input name="type" type="hidden" value="'+services[i]+'">').append(token.clone()).append('<input type="hidden" name="continue" value="continue" />');

    }
    
    $("#"+services[i]+"_form").attr("action",run_addr).attr("method","post").addClass('need').addClass('last').append('<input name="type" type="hidden" value="'+services[i]+'">').append(token.clone());        
    if(serviceNum >1)
    {
    $("form.has_target").submit();
    setInterval(function(){
            $('iframe').each(function(){
                var re = /Continue/;
                var text = $(this).contents().find('body').text();
                   if(re.test(text))
                   {
                        console.log(text);
                        serviceSubmit+=1;
                   }
                   if(serviceSubmit >= serviceNum)
                   {
                         $('form.last').submit();
                   }
                });
            },500);
    }
    else
    {
        $("form.last").submit();
    }


});

$("label.key.type").click(onLabelClicked).find("span").click(onCloseClicked);

function addDest()
{
    var value = $(this).parent().parent().find("input[name='new_dest']").val();
    console.log(value);
    var type;
    if($('.current_key').length == 0)
    {
        type = $("#label-container").find('label').attr('data-value');
    }
    else
        type = $('.current_key').attr('data-value');
    console.log(type);
    $("div."+type).find("#target-selector").append("<option>"+value+"<option>");
    hideAddDialog();
}

function newTask()
{
    location.href=index_addr;
}

function slowlogin()
{
 $("#fullcontent").show();
 $("#logincontent").show();
}
function hidelogin()
{
 $("#fullcontent").hide();
 $("#logincontent").hide();
}

function showAddDialog()
{
 $("#fullcontent").show();
 $("#add_dest").show();
}
function hideAddDialog()
{
 $("#fullcontent").hide();
 $("#add_dest").hide();
}
//TODO:ask server to return a pdf report using post request
function exportPDF()
{
   alert("We are sorry that this function is still developing...."); 
}

function shareData()
{
    var tids =$("input[name='tid']").filter("[checked='checked']").map(function(e){
            return $(this).val();
        }).get().join(" ");
    $.get(share_addr,{tids:tids},function(data){
            
            if(data == 'success')
                alert ("Share data successfully");
            else
                alert ("Failed to share data.");
        
        });
}

$("#fullcontent").css("width", (document.body.clientWidth > document.documentElement.clientWidth ? document.body.clientWidth : document.documentElement.clientWidth)-1);
$("#fullcontent").css("height", document.body.clientHeight > document.documentElement.clientHeight ? document.body.clientHeight : document.documentElement.clientHeight);
