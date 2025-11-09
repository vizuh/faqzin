document.addEventListener("DOMContentLoaded", (event) => {
     let faqs = document.querySelectorAll(".faq-question");
     faqs.forEach((faq) => {
          faq.addEventListener("click", (event) => {
               console.log("FAQ clicked:", event.target);
               let answer = event.target.nextElementSibling;
               console.log("Toggling answer visibility:", answer.style.display);
               answer.style.display = answer.style.display === "block" ? "block" : "none";
          });
     });
});

document.addEventListener("DOMContentLoaded", function () {
     var faqs = document.getElementsByClassName("faq-question");
     for (var i = 0; i < faqs.length; i++) {
          faqs[i].addEventListener("click", function () {
               this.classList.toggle("active");
               var answer = this.nextElementSibling;
               if (answer.style.maxHeight) {
                    // answer.style.maxHeight = null;
                    this.innerHTML = this.innerHTML.replace("-", "+");
               } else {
                    // answer.style.maxHeight = answer.scrollHeight + "px";
                    this.innerHTML = this.innerHTML.replace("+", "-");
               }
          });
     }
});
