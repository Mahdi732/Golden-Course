<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTech - Login & Sign Up</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .hidden {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-4xl w-[30rem]">
            <div class="grid grid-cols-1 gap-6">
                <!-- Login Card -->
                <div id="login-card" class="bg-white p-8 rounded-lg shadow-lg">
                    <div class="text-center">
                        <div class="flex items-center justify-center">
                            <i class="fas fa-book text-blue-600 text-3xl mr-2"></i>
                            <span class="text-3xl font-bold text-gray-900">Login</span>
                        </div>
                    </div>

                    <form class="mt-8 space-y-6">
                        <div class="space-y-4">
                            <div>
                                <label for="login-email" class="block text-sm font-medium text-gray-700">Email</label>
                                <div class="mt-1 relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                    </div>
                                    <input id="login-email" type="email" required 
                                        class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="you@example.com">
                                </div>
                            </div>

                            <div>
                                <label for="login-password" class="block text-sm font-medium text-gray-700">Password</label>
                                <div class="mt-1 relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-lock text-gray-400"></i>
                                    </div>
                                    <input id="login-password" type="password" required
                                        class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="••••••••">
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input id="remember-me" type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="remember-me" class="ml-2 block text-sm text-gray-900">Remember me</label>
                            </div>
                            <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-500">Forgot password?</a>
                        </div>

                        <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Sign in
                        </button>

                        <div class="mt-4">
                            <div class="relative">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-gray-300"></div>
                                </div>
                                <div class="relative flex justify-center text-sm">
                                    <span class="px-2 bg-white text-gray-500">Or continue with</span>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-3">
                                <button class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white hover:bg-gray-50">
                                    <i class="fab fa-google text-red-600"></i>
                                </button>
                                <button class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white hover:bg-gray-50">
                                    <i class="fab fa-facebook text-blue-600"></i>
                                </button>
                                <button class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white hover:bg-gray-50">
                                    <i class="fab fa-github"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-4 text-center">
                        <span class="text-gray-600">Don't have an account?</span>
                        <button onclick="showSignUp()" class="text-blue-600 hover:text-blue-500 font-medium">Sign Up</button>
                    </div>
                </div>

                <!-- Sign Up Card -->
                <div id="signup-card" class="bg-white p-8 rounded-lg shadow-lg hidden">
                    <div class="text-center">
                        <div class="flex items-center justify-center">
                            <i class="fas fa-user-plus text-blue-600 text-3xl mr-2"></i>
                            <span class="text-3xl font-bold text-gray-900">Sign Up</span>
                        </div>
                    </div>

                    <form class="mt-8 space-y-6" action="../prosses/user.php" method="POST">
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="first-name" class="block text-sm font-medium text-gray-700">First Name</label>
                                    <input id="first-name" type="text" required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="John">
                                </div>
                                <div>
                                    <label for="last-name" class="block text-sm font-medium text-gray-700">Last Name</label>
                                    <input id="last-name" type="text" required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Doe">
                                </div>
                            </div>

                            <div>
                                <label for="signup-email" class="block text-sm font-medium text-gray-700">Email</label>
                                <div class="mt-1 relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                    </div>
                                    <input id="signup-email" type="email" required
                                        class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="you@example.com">
                                </div>
                            </div>

                            <div>
                                <label for="signup-password" class="block text-sm font-medium text-gray-700">Password</label>
                                <div class="mt-1 relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-lock text-gray-400"></i>
                                    </div>
                                    <input id="signup-password" type="password" required
                                        class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="••••••••">
                                </div>
                            </div>

                            <div>
                                <label for="confirm-password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <div class="mt-1 relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-lock text-gray-400"></i>
                                    </div>
                                    <input id="confirm-password" type="password" required
                                        class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="••••••••">
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input id="terms" type="checkbox" required
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="terms" class="ml-2 block text-sm text-gray-900">
                                I agree to the <a href="#" class="text-blue-600 hover:text-blue-500">Terms</a> and <a href="#" class="text-blue-600 hover:text-blue-500">Privacy Policy</a>
                            </label>
                        </div>

                        <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Create Account
                        </button>
                    </form>

                    <div class="mt-4 text-center">
                        <span class="text-gray-600">Already have an account?</span>
                        <button onclick="showLogin()" class="text-blue-600 hover:text-blue-500 font-medium">Login</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showSignUp() {
            document.getElementById('login-card').classList.add('hidden');
            document.getElementById('signup-card').classList.remove('hidden');
        }

        function showLogin() {
            document.getElementById('signup-card').classList.add('hidden');
            document.getElementById('login-card').classList.remove('hidden');
        }
    </script>
</body>
</html>