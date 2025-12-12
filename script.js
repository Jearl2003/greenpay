// Handle role toggle (Student vs Canteen Staff)
document.addEventListener("DOMContentLoaded", function () {
    const toggleButtons = document.querySelectorAll(".gp-toggle-btn");
    const roleInput = document.getElementById("roleInput");
    const staffIdInput = document.getElementById("staffId");
    const passwordInput = document.getElementById("password");
    const forgotLink = document.getElementById("forgotLink");
    const studentRegisterLink = document.getElementById("studentRegisterLink");

    let dobFormattingEnabled = false;

    // Function to format DOB input
    function formatDobInput() {
        if (!passwordInput || !dobFormattingEnabled) return;

        // Remove all non-digits
        let digits = this.value.replace(/\D/g, "").slice(0, 8); // max 8 digits: mmddyyyy

        let mm = digits.slice(0, 2);
        let dd = digits.slice(2, 4);
        let yyyy = digits.slice(4, 8);

        let formatted = mm;
        if (dd) formatted += "/" + dd;
        if (yyyy) formatted += "/" + yyyy;

        this.value = formatted;
    }

    // Enable DOB formatting
    function enableDobFormatting() {
        if (passwordInput && !dobFormattingEnabled) {
            passwordInput.addEventListener("input", formatDobInput);
            dobFormattingEnabled = true;
        }
    }

    // Disable DOB formatting
    function disableDobFormatting() {
        if (passwordInput && dobFormattingEnabled) {
            passwordInput.removeEventListener("input", formatDobInput);
            dobFormattingEnabled = false;
        }
    }

    // Helper to update which controls show for current role
    function updateRoleUI(role) {
        if (!forgotLink || !studentRegisterLink) return;

        if (role === "student") {
            // Student login view
            forgotLink.style.display = "none"; // remove forgot password
            studentRegisterLink.style.display = "inline-block"; // show register link
        } else {
            // Canteen staff view
            forgotLink.style.display = "inline-block";
            studentRegisterLink.style.display = "none";
        }

        // Change placeholders and input types according to role
        if (staffIdInput) {
            if (role === "student") {
                staffIdInput.placeholder = "Student ID Number";
            } else {
                staffIdInput.placeholder = "Canteen staff email";
            }
        }

        if (passwordInput) {
            if (role === "student") {
                passwordInput.placeholder = "Password";
                passwordInput.type = "password";
                passwordInput.inputMode = "numeric";
                passwordInput.pattern = "[0-9]{2}/[0-9]{2}/[0-9]{4}";
                passwordInput.title = "Use format mm/dd/yyyy, for example 05/21/2008";
                passwordInput.maxLength = "10";
                enableDobFormatting();
            } else {
                passwordInput.placeholder = "Password";
                passwordInput.type = "password";
                passwordInput.removeAttribute("inputmode");
                passwordInput.removeAttribute("pattern");
                passwordInput.removeAttribute("title");
                passwordInput.removeAttribute("maxlength");
                disableDobFormatting();
            }
        }
    }

    // Set initial UI based on default hidden role value
    const initialRole = roleInput ? roleInput.value || "canteen" : "canteen";
    updateRoleUI(initialRole);

    toggleButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
            const role = btn.dataset.role || "canteen";
            if (roleInput) {
                roleInput.value = role;
            }

            // Toggle active state styles
            toggleButtons.forEach((b) =>
                b.classList.remove("gp-toggle-btn--active")
            );
            btn.classList.add("gp-toggle-btn--active");

            updateRoleUI(role);
        });
    });

    // No extra JS needed for Register; it simply links to register.php
});



