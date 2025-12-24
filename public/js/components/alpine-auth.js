/**
 * Composant Alpine.js pour l'authentification (login/register)
 * Fournit validation temps rÃ©el, feedback visuel et gestion d'Ã©tat
 */
function authManager() {
    return {
        // Ã‰tat du formulaire
        formData: {
            email: '',
            password: '',
            pseudo: '',
            password_confirm: ''
        },

        // Ã‰tats de validation
        errors: {
            email: '',
            password: '',
            pseudo: '',
            password_confirm: ''
        },
        isSubmitting: false,
        showPassword: false,
        showPasswordConfirm: false,
        passwordStrength: 0,
        isCheckingEmail: false,
        isCheckingPseudo: false,

        // Configuration
        isLogin: true, // true pour login, false pour register

        // Initialisation
        init() {
            // DÃ©tecter si on est sur la page login ou register
            this.isLogin = window.location.href.includes('page=login');

            // Restaurer l'email si disponible (login seulement)
            if (this.isLogin && window.location.hash) {
                const hash = window.location.hash.substring(1);
                if (hash.includes('@')) {
                    this.formData.email = hash;
                }
            }

            // PrÃ©parer fonctions dÃ©bouncÃ©es pour Ã©viter trop d'appels rÃ©seau
            this.debouncedCheckEmail = this.debounce(this.checkEmailUniqueness, 500);
            this.debouncedCheckPseudo = this.debounce(this.checkPseudoUniqueness, 500);
        },

        // Helper debounce (retourne une fonction qui attend `wait` ms aprÃ¨s le dernier appel)
        // Utilise closure pour binder correctement le contexte (`this`) du composant
        debounce(fn, wait) {
            let timeout = null;
            const self = this;
            return (...args) => {
                if (timeout) clearTimeout(timeout);
                timeout = setTimeout(() => {
                    try {
                        fn.apply(self, args);
                    } catch (e) {
                        // ignore
                    }
                }, wait);
            };
        },

        // Validation de l'email
        validateEmail() {
            const email = this.formData.email.trim();
            this.errors.email = '';

            if (!email) {
                this.errors.email = 'L\'email est requis';
                return false;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                this.errors.email = 'Format d\'email invalide';
                return false;
            }

            // VÃ©rifier l'unicitÃ© si c'est register (dÃ©bouncÃ©)
            if (!this.isLogin) {
                this.debouncedCheckEmail();
            }

            return true;
        },

        // Validation du mot de passe
        validatePassword() {
            const password = this.formData.password;
            this.errors.password = '';

            if (!password) {
                this.errors.password = 'Le mot de passe est requis';
                return false;
            }

            if (!this.isLogin && password.length < 8) {
                this.errors.password = 'Le mot de passe doit contenir au moins 8 caractÃ¨res';
                return false;
            }

            // Calculer la force du mot de passe (register seulement)
            if (!this.isLogin) {
                this.calculatePasswordStrength();
            }

            return true;
        },

        // Validation du pseudo (register seulement)
        validatePseudo() {
            if (this.isLogin) return true;

            const pseudo = this.formData.pseudo.trim();
            this.errors.pseudo = '';

            if (!pseudo) {
                this.errors.pseudo = 'Le pseudo est requis';
                return false;
            }

            if (pseudo.length < 3) {
                this.errors.pseudo = 'Le pseudo doit contenir au moins 3 caractÃ¨res';
                return false;
            }

            if (pseudo.length > 20) {
                this.errors.pseudo = 'Le pseudo ne peut pas dÃ©passer 20 caractÃ¨res';
                return false;
            }

            // VÃ©rifier les caractÃ¨res autorisÃ©s
            const pseudoRegex = /^[a-zA-Z0-9_-]+$/;
            if (!pseudoRegex.test(pseudo)) {
                this.errors.pseudo = 'Le pseudo ne peut contenir que des lettres, chiffres, _ et -';
                return false;
            }

            // VÃ©rifier l'unicitÃ© (dÃ©bouncÃ©)
            this.debouncedCheckPseudo();

            return true;
        },

        // Validation de la confirmation du mot de passe (register seulement)
        validatePasswordConfirm() {
            if (this.isLogin) return true;

            const confirm = this.formData.password_confirm;
            this.errors.password_confirm = '';

            if (!confirm) {
                this.errors.password_confirm = 'La confirmation du mot de passe est requise';
                return false;
            }

            if (confirm !== this.formData.password) {
                this.errors.password_confirm = 'Les mots de passe ne correspondent pas';
                return false;
            }

            return true;
        },

        // Calcul de la force du mot de passe
        calculatePasswordStrength() {
            const password = this.formData.password;
            let strength = 0;

            // Longueur
            if (password.length >= 8) strength += 25;
            if (password.length >= 12) strength += 25;

            // ComplexitÃ©
            if (/[a-z]/.test(password)) strength += 10;
            if (/[A-Z]/.test(password)) strength += 15;
            if (/[0-9]/.test(password)) strength += 15;
            if (/[^A-Za-z0-9]/.test(password)) strength += 10;

            this.passwordStrength = Math.min(strength, 100);
        },

        // Obtenir la couleur de la force du mot de passe
        getPasswordStrengthColor() {
            if (this.passwordStrength < 30) return 'bg-red-500';
            if (this.passwordStrength < 60) return 'bg-yellow-500';
            if (this.passwordStrength < 80) return 'bg-blue-500';
            return 'bg-green-500';
        },

        // Obtenir le texte de la force du mot de passe
        getPasswordStrengthText() {
            if (this.passwordStrength < 30) return 'Faible';
            if (this.passwordStrength < 60) return 'Moyen';
            if (this.passwordStrength < 80) return 'Bon';
            return 'Excellent';
        },

        // VÃ©rifier l'unicitÃ© de l'email (avec annulation via AbortController)
        async checkEmailUniqueness() {
            const email = this.formData.email.trim();
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return;

            // Annuler la requÃªte prÃ©cÃ©dente si elle existe
            if (this._emailAbortController) {
                try { this._emailAbortController.abort(); } catch (e) { /* ignore */ }
            }
            this._emailAbortController = new AbortController();
            const signal = this._emailAbortController.signal;

            this.isCheckingEmail = true;
            this.errors.email = 'VÃ©rification en cours...';

            try {
                const headers = {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                };
                const tokenEl = document.querySelector('input[name="csrf_token"]');
                if (tokenEl && tokenEl.value) headers['X-CSRF-Token'] = tokenEl.value;

                const response = await fetch(`?page=api_check_unique&email=${encodeURIComponent(email)}`, { headers, signal });
                const text = await response.text();
                let data;
                try { data = JSON.parse(text); } catch (e) {
                    this.isCheckingEmail = false;
                    this.errors.email = 'Erreur de vÃ©rification';
                    console.error('API non JSON pour checkEmailUniqueness:', text.substring(0,200));
                    return;
                }

                this.isCheckingEmail = false;
                if (data.error) {
                    this.errors.email = 'Erreur de vÃ©rification';
                } else if (data.email_taken) {
                    this.errors.email = 'Cet email est dÃ©jÃ  utilisÃ©';
                } else {
                    this.errors.email = '';
                }
            } catch (error) {
                this.isCheckingEmail = false;
                if (error.name === 'AbortError') return; // ignore aborted
                this.errors.email = 'Erreur de vÃ©rification';
                console.error('Erreur lors de la vÃ©rification de l\'email:', error);
            }
        },

        // VÃ©rifier l'unicitÃ© du pseudo (avec annulation via AbortController)
        async checkPseudoUniqueness() {
            const pseudo = this.formData.pseudo.trim();
            if (!pseudo || pseudo.length < 3) return;

            if (this._pseudoAbortController) {
                try { this._pseudoAbortController.abort(); } catch (e) { /* ignore */ }
            }
            this._pseudoAbortController = new AbortController();
            const signal = this._pseudoAbortController.signal;

            this.isCheckingPseudo = true;
            this.errors.pseudo = 'VÃ©rification en cours...';

            try {
                const headers = {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                };
                const tokenEl = document.querySelector('input[name="csrf_token"]');
                if (tokenEl && tokenEl.value) headers['X-CSRF-Token'] = tokenEl.value;

                const response = await fetch(`?page=api_check_unique&pseudo=${encodeURIComponent(pseudo)}`, { headers, signal });
                const text = await response.text();
                let data;
                try { data = JSON.parse(text); } catch (e) {
                    this.isCheckingPseudo = false;
                    this.errors.pseudo = 'Erreur de vÃ©rification';
                    console.error('API non JSON pour checkPseudoUniqueness:', text.substring(0,200));
                    return;
                }

                this.isCheckingPseudo = false;
                if (data.error) {
                    this.errors.pseudo = 'Erreur de vÃ©rification';
                } else if (data.pseudo_taken) {
                    this.errors.pseudo = 'Ce pseudo est dÃ©jÃ  utilisÃ©';
                } else {
                    this.errors.pseudo = '';
                }
            } catch (error) {
                this.isCheckingPseudo = false;
                if (error.name === 'AbortError') return;
                this.errors.pseudo = 'Erreur de vÃ©rification';
                console.error('Erreur lors de la vÃ©rification du pseudo:', error);
            }
        },
        validateForm() {
            // Utiliser logique boolÃ©enne au lieu d'opÃ©rateur bitwise
            let isValid = this.validateEmail() && this.validatePassword();

            if (!this.isLogin) {
                isValid = this.validatePseudo() && isValid;
                isValid = this.validatePasswordConfirm() && isValid;
            }

            return Boolean(isValid);
        },

        // Soumission du formulaire
        async submitForm(event) {
            // EmpÃªcher la soumission si vÃ©rification en cours
            if (this.isCheckingEmail || this.isCheckingPseudo) {
                event.preventDefault();
                return false;
            }

            // Validation cÃ´tÃ© client
            if (!this.validateForm()) {
                event.preventDefault();
                return false;
            }

            // EmpÃªcher la soumission multiple
            if (this.isSubmitting) {
                event.preventDefault();
                return false;
            }

            this.isSubmitting = true;

            // Soumettre le formulaire
            document.querySelector('form[name="login_form"], form[name="register_form"]').submit();
        },

        // Toggle visibilitÃ© mot de passe
        togglePasswordVisibility(field) {
            if (field === 'password') {
                this.showPassword = !this.showPassword;
            } else if (field === 'confirm') {
                this.showPasswordConfirm = !this.showPasswordConfirm;
            }
        },

        // Gestion des Ã©vÃ©nements clavier
        handleKeydown(event, field) {
            if (event.key === 'Enter') {
                event.preventDefault();
                this.submitForm(event);
            }
        },

        // Focus sur le champ suivant (register)
        focusNextField(currentField) {
            const fields = ['pseudo', 'email', 'password', 'password_confirm'];
            const currentIndex = fields.indexOf(currentField);

            if (currentIndex < fields.length - 1) {
                const nextField = fields[currentIndex + 1];
                this.$nextTick(() => {
                    const element = document.getElementById(nextField);
                    if (element) element.focus();
                });
            }
        }
    }
}


