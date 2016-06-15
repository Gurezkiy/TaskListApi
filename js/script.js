$(document).ready(function () {
	$("#ok").click(function(){
		var name = $("#username").val();
		var password = $("#userpassword").val();
		$.ajax({            
            /* адрес файла-обработчика запроса */
            url: 'testing.php',
            /* метод отправки данных */
            method: 'GET',
			data:{"name":name,"password":password},
			crossDomain: true,
            /* что нужно сделать по факту выполнени¤ запроса */            
            }).done(function(data){
				$("#result").empty();
				$("#result").append(data);
			}); 
	});
});