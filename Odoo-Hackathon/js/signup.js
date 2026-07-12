function signupUser(){

    let name = document.getElementById("name").value;

    let email = document.getElementById("email").value;

    let password = document.getElementById("password").value;


    if(name && email && password){

        alert("Account Created Successfully. Please Login");


        window.location.href="index.html";


    }
    else{

        alert("Please fill all details");

    }


    return false;

}