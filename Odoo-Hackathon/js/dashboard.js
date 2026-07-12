/* ===============================
   AssetFlow Dashboard JS
================================ */



// ===============================
// Asset Distribution Chart
// ===============================

const assetChart = document.getElementById("assetChart");


if(assetChart){

new Chart(assetChart, {

    type:"doughnut",

    data:{

        labels:[
            "Allocated",
            "Available",
            "Maintenance"
        ],

        datasets:[{

            data:[
                975,
                273,
                42
            ]

        }]

    },


    options:{

        responsive:true,

        plugins:{

            legend:{

                position:"bottom"

            }

        }

    }


});

}




// ===============================
// Monthly Growth Chart
// ===============================


const growthChart = document.getElementById("growthChart");


if(growthChart){


new Chart(growthChart, {


    type:"line",


    data:{


        labels:[

            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun"

        ],



        datasets:[{


            label:"Assets Added",


            data:[

                120,
                250,
                400,
                650,
                900,
                1248

            ],



            tension:0.4


        }]



    },



    options:{


        responsive:true,


        plugins:{


            legend:{


                position:"bottom"


            }


        }



    }



});


}





// ===============================
// Sidebar Active Menu
// ===============================


const menuItems = document.querySelectorAll(".sidebar ul li");


menuItems.forEach(item=>{


    item.addEventListener("click",()=>{


        menuItems.forEach(menu=>{

            menu.classList.remove("active");

        });


        item.classList.add("active");


    });


});





// ===============================
// Notification Animation
// ===============================


const notifications = document.querySelectorAll(".notification");


notifications.forEach((item,index)=>{


    item.style.animationDelay = `${index * 0.2}s`;


});