<x-app-layout>
  
    <div class="py-8 bg-gray-100 min-h-screen">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-lg p-8 text-white mb-8">
                <h1 class="text-3xl font-bold mb-2">
                    Welcome Back, {{ Auth::user()->name }} 👋
                </h1>

                <p class="text-blue-100">
                    Manage your account, applications, jobs and activities from your dashboard.
                </p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">
                                Profile Completion
                            </p>

                            <h3 class="text-3xl font-bold text-green-600">
                                85%
                            </h3>
                        </div>

                        <div class="text-green-500 text-4xl">
                            👤
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">
                                Applied Jobs
                            </p>

                            <h3 class="text-3xl font-bold text-blue-600">
                                12
                            </h3>
                        </div>

                        <div class="text-blue-500 text-4xl">
                            💼
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">
                                Saved Jobs
                            </p>

                            <h3 class="text-3xl font-bold text-purple-600">
                                8
                            </h3>
                        </div>

                        <div class="text-purple-500 text-4xl">
                            ⭐
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">
                                Notifications
                            </p>

                            <h3 class="text-3xl font-bold text-red-600">
                                5
                            </h3>
                        </div>

                        <div class="text-red-500 text-4xl">
                            🔔
                        </div>
                    </div>
                </div>

            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-6">
                    Quick Actions
                </h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                    <a href="#"
                       class="bg-blue-600 text-white p-4 rounded-lg text-center hover:bg-blue-700 transition">
                        Update Profile
                    </a>

                    <a href="#"
                       class="bg-green-600 text-white p-4 rounded-lg text-center hover:bg-green-700 transition">
                        Browse Jobs
                    </a>

                    <a href="#"
                       class="bg-purple-600 text-white p-4 rounded-lg text-center hover:bg-purple-700 transition">
                        Upload Resume
                    </a>

                    <a href="#"
                       class="bg-orange-600 text-white p-4 rounded-lg text-center hover:bg-orange-700 transition">
                        View Applications
                    </a>

                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Profile Information -->
                <div class="lg:col-span-1">

                    <div class="bg-white rounded-xl shadow-md p-6">

                        <div class="text-center">

                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=2563eb&color=fff"
                                 class="w-24 h-24 rounded-full mx-auto mb-4">

                            <h3 class="text-xl font-bold text-gray-800">
                                {{ Auth::user()->name }}
                            </h3>

                            <p class="text-gray-500">
                                {{ Auth::user()->email }}
                            </p>

                        </div>

                        <hr class="my-4">

                        <div class="space-y-3">

                            <div class="flex justify-between">
                                <span>Member Since</span>
                                <span>{{ Auth::user()->created_at->format('d M Y') }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span>Status</span>
                                <span class="text-green-600 font-semibold">
                                    Active
                                </span>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- Recent Activities -->
                <div class="lg:col-span-2">

                    <div class="bg-white rounded-xl shadow-md p-6">

                        <h3 class="text-xl font-bold text-gray-800 mb-4">
                            Recent Activities
                        </h3>

                        <div class="space-y-4">

                            <div class="border-l-4 border-blue-500 pl-4">
                                <p class="font-semibold">
                                    Profile Updated
                                </p>
                                <span class="text-sm text-gray-500">
                                    2 hours ago
                                </span>
                            </div>

                            <div class="border-l-4 border-green-500 pl-4">
                                <p class="font-semibold">
                                    Applied for Software Developer Position
                                </p>
                                <span class="text-sm text-gray-500">
                                    Yesterday
                                </span>
                            </div>

                            <div class="border-l-4 border-purple-500 pl-4">
                                <p class="font-semibold">
                                    Resume Uploaded Successfully
                                </p>
                                <span class="text-sm text-gray-500">
                                    3 days ago
                                </span>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>