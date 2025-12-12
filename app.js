// Handle role toggle (Student vs Canteen Staff)
document.addEventListener("DOMContentLoaded", function () {
    const toggleButtons = document.querySelectorAll(".gp-toggle-btn");
    const roleInput = document.getElementById("roleInput");
    const staffIdInput = document.getElementById("staffId");

    toggleButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
            const role = btn.dataset.role || "canteen";
            roleInput.value = role;

            // Toggle active state styles
            toggleButtons.forEach((b) =>
                b.classList.remove("gp-toggle-btn--active")
            );
            btn.classList.add("gp-toggle-btn--active");

            // Change placeholder according to role
            if (role === "student") {
                staffIdInput.placeholder = "Student ID Number";
            } else {
                staffIdInput.placeholder = "Canteen Staff ID Number";
            }
        });
    });
});


