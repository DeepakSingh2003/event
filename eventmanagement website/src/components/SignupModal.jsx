import React, { useEffect, useState } from "react";
import { X } from "lucide-react";

// Use Vite's environment variable
const API_BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";

const SignupModal = ({ onClose }) => {
  const [email, setEmail] = useState("");
  const [customAlert, setCustomAlert] = useState({
    show: false,
    message: "",
  });

  useEffect(() => {
    document.body.style.overflow = "hidden";
    return () => {
      document.body.style.overflow = "unset";
    };
  }, []);

  const showAlert = (message) => {
    setCustomAlert({ show: true, message });
  };

  const closeAlert = () => {
    setCustomAlert({ show: false, message: "" });
  };
  const getCookie = (name) => {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
      return decodeURIComponent(parts.pop().split(";").shift());
    }
    return null;
  };

  const handleEmailLogin = async () => {
    if (!email) {
      showAlert("Please enter your email");
      return;
    }

    try {
      await fetch(`${API_BASE_URL}/sanctum/csrf-cookie`, {
        method: "GET",
        credentials: "include",
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      const xsrfToken = getCookie("XSRF-TOKEN");

      const res = await fetch(`${API_BASE_URL}/login`, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-XSRF-TOKEN": xsrfToken || "",
        },
        body: JSON.stringify({
          email,
          password: "password",
        }),
      });

      if (res.ok) {
        showAlert("Login successful ✅");
        setTimeout(() => {
          onClose();
          window.location.reload();
        }, 1000);
      } else {
        console.log("Login status:", res.status);
        const data = await res.text();
        console.log(data);
        showAlert("Login failed ❌");
      }
    } catch (error) {
      console.error(error);
      showAlert("Server error ❌");
    }
  };

  return (
    <>
      <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
        <div className="w-full max-w-5xl bg-white rounded-2xl overflow-hidden shadow-2xl flex">
          {/* LEFT IMAGE */}
          <div className="hidden md:block w-1/2">
            <img
              src="https://images.unsplash.com/photo-1515169067868-5387ec356754?q=80&w=1200"
              alt="people"
              className="h-full w-full object-cover"
            />
          </div>

          {/* RIGHT CONTENT */}
          <div className="w-full md:w-1/2 p-8 relative">
            {/* Close */}
            <button
              onClick={onClose}
              className="absolute top-4 right-4 text-gray-400 hover:text-gray-600"
            >
              <X size={22} />
            </button>

            {/* Heading */}
            <h2 className="text-3xl font-semibold text-gray-800 mb-2">
              Sign In - A new world awaits
            </h2>
            <p className="text-gray-500 mb-6">Join a community of users</p>

            {/* GOOGLE LOGIN (Laravel redirect) */}
            <a
              href={`${API_BASE_URL}/auth/google`}
              className="w-full flex items-center justify-center gap-3 border rounded-lg py-3 hover:bg-orange-50 transition"
            >
              <img
                src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg"
                className="w-5 h-5"
                alt="google"
              />
              Continue with Google
            </a>

            {/* Divider */}
            <div className="flex items-center my-6">
              <div className="flex-1 h-px bg-gray-300"></div>
              <span className="px-3 text-sm text-orange-500 font-medium">
                or sign in with your email
              </span>
              <div className="flex-1 h-px bg-gray-300"></div>
            </div>

            {/* EMAIL */}
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="Enter your email here"
              className="w-full border rounded-lg px-4 py-3 mb-4 focus:outline-none focus:ring-2 focus:ring-orange-500"
            />

            <button
              onClick={handleEmailLogin}
              className="w-full bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-lg font-medium transition shadow-md"
            >
              Continue
            </button>
          </div>
        </div>
      </div>

      {/* ALERT */}
      {customAlert.show && (
        <div className="fixed inset-0 flex items-center justify-center z-[60] bg-black/50 backdrop-blur-sm">
          <div className="bg-white rounded-xl shadow-xl max-w-sm w-full mx-4 p-5">
            <p className="text-gray-700 mb-4">{customAlert.message}</p>
            <button
              onClick={closeAlert}
              className="bg-orange-500 text-white px-4 py-2 rounded"
            >
              OK
            </button>
          </div>
        </div>
      )}
    </>
  );
};

export default SignupModal;
