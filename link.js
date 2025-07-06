 $('#logoutB').click(function (){
    window.location.href = "/PKMS/PKMS_Complete/Onyedikachi's%20code/signin.php";
});

function displayManager(role){
    if (role != "Manager"){
        $('#ManageButton').css('display','none');
    }else {
        $('#ManageButton').css('display','block');
    }          
}
displayManager(role);

//manger page essensials
$('#ManageButton').click(function (){
    window.location.href = "/PKMS/PKMS(Manager)/ManagerLanding.php";
});

//manger page essensials
$('#ProdButton').click(function (){
    window.location.href = "/PKMS/PKMS(Productivity)/ProductivityLanding.php";
});

//manger page essensials
$('#KnowledgeButton').click(function (){
    window.location.href = "/standard_index.php";
});

$('#RemindersButton').click(function (){
    window.location.href = "/remindersLanding.php";
});

$('#InviteButton').click(function (){
    window.location.href = "/invite.php";
});