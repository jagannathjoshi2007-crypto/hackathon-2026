// ===============================
// AssetFlow Login Page
// ===============================

// Password Show / Hide

const password = document.querySelector('input[type="password"]');

if(password){

    const eye = document.createElement("i");

    eye.className = "fa-solid fa-eye";

    eye.style.cursor = "pointer";

    eye.style.marginLeft = "10px";

    password.parentElement.appendChild(eye);

    eye.addEventListener("click",()=>{

        if(password.type==="password"){

            password.type="text";

            eye.classList.remove("fa-eye");

            eye.classList.add("fa-eye-slash");

        }else{

            password.type="password";

            eye.classList.remove("fa-eye-slash");

            eye.classList.add("fa-eye");

        }

    });

}

// Login Validation

const form=document.querySelector("form");

form.addEventListener("submit",function(e){

    const email=document.querySelector("input[type='email']").value;

    const pass=document.querySelector("input[type='password']").value;

    if(email==="" || pass===""){

        e.preventDefault();

        alert("Please fill all fields.");

    }

});