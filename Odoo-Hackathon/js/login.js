function loginUser(){

    let email = document.getElementById("email").value;

    let password = document.getElementById("password").value;


    if(email === "admin@gmail.com" && password === "123456"){


        localStorage.setItem("user","Admin");


        window.location.href="dashboard.html";


    }
    else{


        alert("Invalid Email or Password");


    }


    return false;

}