	document.addEventListener("DOMContentLoaded",  () =>{
		const body =  document.querySelector("body"),
			formOpenBtn = document.querySelector("#form-open"),
			formCloseBtn = document.querySelector("#form-close"),
			formContainer = document.querySelector(".form-container"),
			signUpToggle = document.querySelector("#signup-toggle"),
			loginToggle = document.querySelector("#login-toggle"),
			recoverToggle = document.querySelector("#recover-toggle"),
			loader = document.getElementById('loader-overlay'),
			registerForm = document.querySelector(".register-box form"),
       		loginForm = document.querySelector('.login-box form'),
			recoveryForm = document.querySelector(".recovery-box form"),
			resetForm = document.querySelector(".reset-box form"),
			verifyBtn = document.getElementById('verify-btn'),
			backToLoginBtn = document.querySelector(".back-to-login"),
			tokenInput = document.getElementById('token-input');


			let generatedToken = "";
	
			if (formOpenBtn) formOpenBtn.addEventListener("click", () => body.classList.add("show-form"));
    		if (formCloseBtn) formCloseBtn.addEventListener("click", () => body.classList.remove("show-form"));

			if (signUpToggle) {
		        signUpToggle.addEventListener("click", (e) => {
		            e.preventDefault();
		            formContainer.classList.add("active");
		        });
		    }

		    if (loginToggle) {
		        loginToggle.addEventListener("click", (e) => {
		            e.preventDefault();
		            formContainer.classList.remove("active");
		        });
		    }

			if (recoverToggle) {
				recoverToggle.addEventListener("click", (e) => {
					e.preventDefault();
					formContainer.classList.remove("active"); 
					formContainer.classList.add("show-recovery"); 
				});
			}

			if (backToLoginBtn) {
			    backToLoginBtn.addEventListener("click", (e) => {
			        e.preventDefault();
			        formContainer.classList.remove("active", "show-recovery", "show-reset");
			    });
			}

			if(recoveryForm){
				recoveryForm.addEventListener("submit", async(e) => {
					e.preventDefault();
					if(loader) loader.classList.add('active');

					const formData = new FormData(recoveryForm);
					const data = Object.fromEntries(formData.entries());

					try{
						const response = await fetch('/Dee-Auth-System/api/passwordRecovery.php', {
							method: 'POST',
							headers: { 
								'Content-Type': 'application/json'
							},
							body: JSON.stringify(data)
						});

						const result = await response.json();
						if(loader) loader.classList.remove('active');
						if(response.ok && result.status === "success"){
							showNotification(result.message, 'success');
							generatedToken = result.token;

							setTimeout(() => {
					            showNotification(`Check your email for token`, 'success');
					        }, 500);

							document.getElementById("token-verification-group").style.display = "block";
							document.getElementById("recover-btn").style.display = "none";
							document.getElementById("verify-btn").style.display = "block";

						}else{
							showNotification(result.message, 'error');
						}
					}catch(error){
						if(loader) loader.classList.remove('active');
						showNotification("An error occurred. Please try again.", 'error');
					}
				});
			}

			if(verifyBtn){
				verifyBtn.addEventListener("click", () => {
					const enteredToken = tokenInput.value.trim();	
					if (enteredToken === generatedToken) {
						formContainer.classList.remove("active");
           				formContainer.classList.remove("show-recovery");
						formContainer.classList.add("show-reset");

					const emailVal = recoveryForm.querySelector('input[name="email"]').value;
		            const hiddenEmail = document.getElementById('reset-email-hidden');
		            if(hiddenEmail) hiddenEmail.value = emailVal;

		            showNotification("Verified! Please reset your password.", "success");
		            
					} else {
						showNotification("Invalid token. Please try again.", 'error');
					}
				});
			}

			if (resetForm) {
				resetForm.addEventListener("submit", async (e) => {
					e.preventDefault();

					const newPassword = resetForm.querySelector('input[name="password"]').value;
					const confirmPassword = resetForm.querySelector('input[name="confirmPassword"]').value;
					if (newPassword !== confirmPassword) {
						showNotification("Passwords do not match.", 'error');
						return;
					}
					if (loader) loader.classList.add('active');

					const formData = new FormData(resetForm);
					const data = Object.fromEntries(formData.entries());

					try {
						const response = await fetch('/Dee-Auth-System/api/passReset.php', {
							method: 'POST',
							headers: { 'Content-Type': 'application/json' },
							body: JSON.stringify(data)
						});
						const result = await response.json();
						if (loader) loader.classList.remove('active');

						if (result.status === "success") {
							showNotification("Password reset successful!", "success");
							setTimeout(() => {
								formContainer.classList.remove("show-reset");
							}, 1500);
						} else {
							showNotification(result.message, 'error');
						}
					} catch (error) {
						if (loader) loader.classList.remove('active');
						showNotification("Request failed.", 'error');
					}
				});
			}

// Registration API Handler
		if(registerForm){
			registerForm.addEventListener("submit",  async(e)  =>{
				e.preventDefault();

				const password = registerForm.querySelector('input[name="password"]').value;
				const confirmPassword = registerForm.querySelector('input[name="confirmPassword"]').value;
				if (password !== confirmPassword) {
					showNotification("Passwords do not match.", 'error');
					return;
				}

				if (loader) loader.classList.add('active');

				const formData = new FormData(registerForm);
				const data = Object. fromEntries(formData.entries());

				try{
					const response = await fetch('/Dee-Auth-System/api/register.php', {
						method: 'POST',
						headers: {
							'Content-Type' : 'application/json'
						},
						body:  JSON.stringify(data)
					});

					const  result = await response.json();
					if (loader) loader.classList.remove('active');

					if(response.ok && result.status === "success"){
						showNotification(result.message, 'success');
						registerForm.reset();
						formContainer.classList.remove("active");
					}else{
						showNotification(result.message, 'error')
					}
				}catch(error){
						showNotification("Connection error. Please try again.", 'error');
				}
			})
		}

//Login API Handler
		if(loginForm){
			loginForm.addEventListener('submit', async(e) =>{
				e.preventDefault();

				if (loader) loader.classList.add('active');

				const formData = new FormData(loginForm);
				const data = Object. fromEntries(formData.entries());

				try{
					const response = await fetch('/Dee-Auth-System/api/login.php', {
						method: 'POST',
						headers: {
							'Content-Type' : 'application/json' 
						},
						body: JSON.stringify(data)
					});
					const result = await response.json();
					if (loader) loader.classList.remove('active');

					if(response.ok && result.status === 'success'){
						showNotification(result.message, 'success');
					        setTimeout(() => {
					            window.location.href = 'dashboard.php';
					        }, 1000);
					}else {
				        showNotification(result.message, 'error');
				    }
				}catch(error){
					if (loader) loader.classList.remove('active');
						showNotification("Server error. Please try again later.", 'error');
					}
			})
		}

			const showNotification = (message, type = 'success') => {
		    const container = document.getElementById('notification-container');
		    const notification = document.createElement('div');
		    
		    notification.className = `notification ${type}`;
		    notification.textContent = message;
		    
		    container.appendChild(notification);
		  		  setTimeout(() => {
			        notification.classList.add('fade-out');
			        setTimeout(() => notification.remove(), 500);
			    }, 10000);
			};

	});

