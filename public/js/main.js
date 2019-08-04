
$(document).ready(function() {

	loader();
	sendEmail();





function loader(){

	$( ".go" ).click(function() {
		var email = $(".movie-title").val();

		if (email.length >1) {
			$( ".buffer" ).removeClass( "loader" );	
		}
		
	});

}

function sendEmail(){


	$( ".sendEmail" ).click(function() {

	var email = $("#email-req").val();
	var title = $("#title-req").val(); 
	
	if (email.length >1 && title.length >1) {
		$('.notification').html('<i style="color: blue;">Email is being sent!</i>');
	
		$.ajax({
            url:"/api/send/"+email+"/movie/"+title,
            type: "GET",
            async: true,
            success: function (data)
            {
                console.log(data);

                if (data == 200) {
                	$('.notification').html('<i style="color: green;">Email has been sent</i>');
                }else{
                	$('.notification').html('<i style="color: red;">Email has been Failed!</i>');
                }
                
            },
            error: function(xhr, textStatus, errorThrown){
                console.log('request failed');
            }
        });

	}
		

	});
}





});


