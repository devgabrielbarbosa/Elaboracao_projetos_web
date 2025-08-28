// menu haburger

 const toggleBtn = document.getElementById("menu-toggle");
  const menuItems = document.getElementById("menu-items");

  toggleBtn.addEventListener("click", () => {
    menuItems.classList.toggle("show");
  });

// slider