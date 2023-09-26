class Bootnavbar{

  defaultOption = {
    selector: "main_navbar",
    animation: true,
    animateIn: "animate__fadeIn",
  };

  init (options) {
    let bnOptions = { ...this.defaultOption, ...options };
    var dropdowns = document
      .getElementById(bnOptions.selector)
      .getElementsByClassName("dropdown");

    Array.prototype.forEach.call(dropdowns, (item) => {
      //add animation
      if (bnOptions.animation) {
        const element = item.querySelector(".dropdown-menu");
        element.classList.add("animate__animated");
        element.classList.add(bnOptions.animateIn);
      }

      //hover effects
      item.addEventListener("mouseover", function () {
        this.classList.add("show");
        const element = this.querySelector(".dropdown-menu");
        element.classList.add("show");
      });

      item.addEventListener("mouseout", function () {
        this.classList.remove("show");
        const element = this.querySelector(".dropdown-menu");
        element.classList.remove("show");
      });
    });
  };
}
export default Bootnavbar;
