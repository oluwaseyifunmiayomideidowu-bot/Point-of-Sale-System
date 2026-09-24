const pageScrollButton =
  document.getElementById("pageScrollButton");

if (pageScrollButton) {

  function updateScrollButton() {

    const scrollTop = window.scrollY;

    const pageHeight = document.documentElement.scrollHeight;
    const windowHeight = window.innerHeight;

    const atBottom =
      scrollTop + windowHeight >= pageHeight - 10;

    if (atBottom) {

      // At bottom → show UP
      pageScrollButton.textContent = "↑";

      pageScrollButton.setAttribute(
        "aria-label",
        "Go to top"
      );

      pageScrollButton.setAttribute(
        "title",
        "Go to top"
      );

    } else {

      // At top or somewhere above bottom → show DOWN
      pageScrollButton.textContent = "↓";

      pageScrollButton.setAttribute(
        "aria-label",
        "Go to bottom"
      );

      pageScrollButton.setAttribute(
        "title",
        "Go to bottom"
      );
    }
  }


  pageScrollButton.addEventListener("click", () => {

    const scrollTop = window.scrollY;

    const pageHeight =
      document.documentElement.scrollHeight;

    const windowHeight =
      window.innerHeight;

    const atBottom =
      scrollTop + windowHeight >= pageHeight - 10;


    if (atBottom) {

      // Go to top
      window.scrollTo({
        top: 0,
        behavior: "smooth"
      });

    } else {

      // Go to bottom
      window.scrollTo({
        top: pageHeight,
        behavior: "smooth"
      });

    }

  });


  window.addEventListener("scroll", updateScrollButton);

  window.addEventListener("resize", updateScrollButton);

  // Set the correct arrow when page loads
  updateScrollButton();
}