$(document).ready(function(){
    
    function onSrctypeChange()
    {
        if($(this).val()==1)
        {
            if($(this).parent().parent().find("div.added").length!=0)
            {
                $("div.added").show();
            }
            else
            {
                var added = $("div#probe_selector").clone();
                added.addClass('added');
                $(this).parent().parent().append(added);
                added.show();
            }
        }
        else
        {
            $("div.added").hide();
        }
    }
    
    function onAddButtonClicked()
    {
        $("input[name='adddest']").val('');
        $("div#newdest").modal("show");
    }

    function onDeleteButtonClicked()
    {
        $("#destination_selector option:selected").remove();
        $("#destination_selector option:last").attr('selected',true);
        if($("#destination_selector option:selected").text()=='Add New Destination...')
            $(this).hide();
        //$(this).hide();
    }

    function onDestinationSelected()
    {
        if($(this).find("option:selected").text()!='Add New Destination...')
        {
            $("#deletedest").show();
        }
        else
        {
            $("#deletedest").hide();
        }
    }

    $('select[name="type"]').val('ping');

	$('a[href="addprobe"]').click(function(e){

		e.preventDefault();
		$("#popup").bPopup();
	});

	$('a[href="addtask"]').click(function(e){
		e.preventDefault();
        $('#modal').modal('show');
	});

    $("#save_added").click(function(){
        var dest = $("input[name='adddest']").val();
        if(dest != '')
        {
            $("#destination_selector").append('<option name="dest" value="'+dest+'">'+dest+'</option>');
            $("#destination_selector").find('option:last').attr("selected",true);
            $("#destination_selector").trigger('change');
        }
        $("#newdest").modal('hide');
           
    })
	//$('#type').change(function(){
	//	$.get(argAddr,{type:$('#type').val()},function(data){
	//		var extras = $("#extraargs");
	//		var adding = $(data);
    //        extras.html("");
            //extras.append(adding);
	//	})
	//});

    function initListener()
    {

                    $('#args').html(data);
                    $("#args").find("input[name='srctype']").change(onSrctypeChange);
                    $("#args").find("#adddest").click(onAddButtonClicked);
                    $("#args").find("#deletedest").click(onDeleteButtonClicked);
                    $("#args").find("#destination_selector").change(onDestinationSelected);
    }
    $('#modal').on('show',function(){
            $.get(argAddr,{type:"ping"},function(data){
                    $('#args').html(data);
                    $("#args").find("input[name='srctype']").change(onSrctypeChange);
                    $("#args").find("#adddest").click(onAddButtonClicked);
                    $("#args").find("#deletedest").click(onDeleteButtonClicked);
                    $("#args").find("#destination_selector").change(onDestinationSelected);

                });
        });
    $('select[name="type"]').change(function(){
                var type = $("select[name='type']").val();
                $.get(argAddr,{type:type},function(data){
                        $('#args').html(data);
                        //$("$args").find("input[name='srctype']").change(onSrctypeChange);
                        initListener();
                    });
            });
    $('#modal-submit').click(function(){
            
                if($("select[name='dest']").val())
                {
                    ;
                }
                else
                {
                    var input = $('input[name="dest"]');
                    var dests = "";
                    $("#destination_selector").find("option:first").remove();
                    input.val($("#destination_selector option").map(function(){
                                return $(this).text();
                            }).get().join(' '));
                    alert(input.val());
                }
               $('form').submit();
            });

    $('a[href="exportConfigure"]').click(function(e){
            e.preventDefault();
            var pid = $(this).attr('data-value');
            //var form = $('form').attr('action',configureAddr).attr('method','post').style('display'); 
            //form.append("<input type='hidden' name='pid' value='"+pid+"'/>");
            //form.submit();
            if($("#config_form").length == 0)
            {
                var form = $('<form></form>').attr('id','config_form').attr('action',configureAddr).attr('method','post').css('display','none').append("<input type='hidden' name='pid' value='"+pid+"'/>");
                $('body').append(form);
                form.submit();
            }
            else
            {
                var form = $("#config_from");
                form.find('input').val(''+pid);
                form.submit();
            }
            
            });
})
