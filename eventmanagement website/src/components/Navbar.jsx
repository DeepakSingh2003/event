// components/Navbar.jsx
import { useState, useRef, useEffect } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { Heart } from "lucide-react";
import LocationModal from "./LocationModal";
import SearchSuggestions from "./SearchSuggestions";
import SignupModal from "./SignupModal";
import useWishlist from "../hooks/useWishlist";

// Use Vite's environment variable
const API_BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";

const Navbar = ({
  selectedCountry,
  onCountryChange,
}) => {
  const navigate = useNavigate();
  const location = useLocation();
  const wishlist = useWishlist();
  const [user, setUser] = useState(null);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [isLocationModalOpen, setIsLocationModalOpen] = useState(false);
  const [isSignupModalOpen, setIsSignupModalOpen] = useState(false);
  const [isSearchOpen, setIsSearchOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");
  const [isProfileDropdownOpen, setIsProfileDropdownOpen] = useState(false);
  const [customAlert, setCustomAlert] = useState({ show: false, message: "" });
  const [loadingUser, setLoadingUser] = useState(true);
  const [isScrolled, setIsScrolled] = useState(false);
  const mobileMenuRef = useRef(null);
  const profileDropdownRef = useRef(null);
  const isHomePage = location.pathname === "/";

  useEffect(() => {
    const fetchUser = async () => {
      try {
        const response = await fetch(`${API_BASE_URL}/me`, {
          method: "GET",
          credentials: "include",
          headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
          },
        });

        if (response.status === 401) {
          setUser(null);
          return;
        }

        if (!response.ok) {
          console.log("Status:", response.status);
          setUser(null);
          return;
        }

        const data = await response.json();

        setUser({
          name: data.name,
          email: data.email,
          photo: data.photo,
        });
      } catch (error) {
        console.error("Error fetching user:", error);
        setUser(null);
      } finally {
        setLoadingUser(false);
      }
    };

    fetchUser();
  }, []);

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 24);
    };

    handleScroll();
    window.addEventListener("scroll", handleScroll, { passive: true });

    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  // Close mobile menu when clicking outside
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (
        mobileMenuRef.current &&
        !mobileMenuRef.current.contains(event.target) &&
        isMobileMenuOpen
      ) {
        setIsMobileMenuOpen(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, [isMobileMenuOpen]);

  // Close profile dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (
        profileDropdownRef.current &&
        !profileDropdownRef.current.contains(event.target) &&
        isProfileDropdownOpen
      ) {
        setIsProfileDropdownOpen(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, [isProfileDropdownOpen]);

  // Prevent body scroll when mobile menu is open
  useEffect(() => {
    if (isMobileMenuOpen) {
      document.body.style.overflow = "hidden";
    } else {
      document.body.style.overflow = "unset";
    }
    return () => {
      document.body.style.overflow = "unset";
    };
  }, [isMobileMenuOpen]);

  // Custom alert helper
  const showAlert = (message) => {
    setCustomAlert({ show: true, message });
  };

  const closeAlert = () => {
    setCustomAlert({ show: false, message: "" });
  };

  // Action handlers using custom alert
  const handleCityChange = () => setIsLocationModalOpen(true);
  const handleSignupClick = () => setIsSignupModalOpen(true);
  const handleEvents = () => {
    setIsMobileMenuOpen(false);
    navigate("/search");
  };
  const handleSupport = () => showAlert("Support / Help");
  const handleAppDownload = () =>
    window.open(
      "https://play.google.com/store/apps/details?id=com.amitech.allevents",
      "_blank",
    );
  const handleBookNow = () =>
    showAlert("Proceed to book event tickets");
  const handleSearch = (e) => {
    e.preventDefault();
    setIsSearchOpen(true);
  };

  const handleSearchChange = (event) => {
    setSearchQuery(event.target.value);
    setIsSearchOpen(true);
  };

  const closeSearchSoon = () => {
    window.setTimeout(() => setIsSearchOpen(false), 120);
  };

  const handleBookingHistory = () => {
    navigate("/my-bookings");
    setIsProfileDropdownOpen(false);
    setIsMobileMenuOpen(false);
  };

  const getCookie = (name) => {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
      return decodeURIComponent(parts.pop().split(";").shift());
    }
    return null;
  };

  const handleLogout = async () => {
    try {
      await fetch(`${API_BASE_URL}/sanctum/csrf-cookie`, {
        credentials: "include",
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      const xsrfToken = getCookie("XSRF-TOKEN");

      const response = await fetch(`${API_BASE_URL}/logout`, {
        method: "POST",
        credentials: "include",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-XSRF-TOKEN": xsrfToken || "",
        },
      });

      if (response.ok) {
        setUser(null);
        setIsProfileDropdownOpen(false);
        setIsMobileMenuOpen(false);
        showAlert("Logged out successfully");
      } else {
        console.log("Logout status:", response.status);
      }
    } catch (err) {
      console.error("Logout error:", err);
    }
  };

  // Helper: Get user display name or email
  const getUserDisplayName = () => {
    if (user?.name) return user.name;
    if (user?.email) return user.email.split("@")[0];
    return "User";
  };

  // Helper: Get profile image URL or fallback
  const getProfileImage = () => {
    if (user?.photo) return user.photo;
    return "https://cdn2.allevents.in/transup/ab/ced64bf76d429aaa513418ff72f873/profile.png";
  };

  // Icons with dark theme styling (unchanged)
  const Icons = {
    marker: () => (
      <svg
        className="w-4 h-4 mr-1"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
      >
        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
        <circle cx="12" cy="10" r="3" />
      </svg>
    ),
    angleDown: () => (
      <svg
        className="w-4 h-4 ml-1"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
      >
        <polyline points="6 9 12 15 18 9" />
      </svg>
    ),
    search: () => (
      <svg
        className="w-5 h-5"
        viewBox="0 0 18 18"
        fill="none"
        stroke="currentColor"
        strokeWidth="1.4"
      >
        <path d="M15.75 15.75L11.25 11.25M2.25 7.5C2.25 8.18944 2.3858 8.87213 2.64963 9.50909C2.91347 10.146 3.30018 10.7248 3.78769 11.2123C4.2752 11.6998 4.85395 12.0865 5.49091 12.3504C6.12787 12.6142 6.81056 12.75 7.5 12.75C8.18944 12.75 8.87213 12.6142 9.50909 12.3504C10.146 12.0865 10.7248 11.6998 11.2123 11.2123C11.6998 10.7248 12.0865 10.146 12.3504 9.50909C12.6142 8.87213 12.75 8.18944 12.75 7.5C12.75 6.81056 12.6142 6.12787 12.3504 5.49091C12.0865 4.85395 11.6998 4.2752 11.2123 3.78769C10.7248 3.30018 10.146 2.91347 9.50909 2.64963C8.87213 2.3858 8.18944 2.25 7.5 2.25C6.81056 2.25 6.12787 2.3858 5.49091 2.64963C4.85395 2.91347 4.2752 3.30018 3.78769 3.78769C3.30018 4.2752 2.91347 4.85395 2.64963 5.49091C2.3858 6.12787 2.25 6.81056 2.25 7.5Z" />
      </svg>
    ),
    ticket: () => (
      <svg
        className="w-4 h-4 mr-2"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
      >
        <path d="M2 8.5h20M2 12h20M2 15.5h20" strokeLinecap="round" />
        <rect x="2" y="5" width="20" height="14" rx="2" ry="2" />
        <path d="M7 5v14M17 5v14" strokeLinecap="round" />
      </svg>
    ),
    film: () => (
      <svg
        className="w-4 h-4 mr-2"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
      >
        <rect x="2" y="4" width="20" height="16" rx="2" />
        <line x1="10" y1="4" x2="10" y2="20" />
        <line x1="14" y1="4" x2="14" y2="20" />
        <line x1="2" y1="10" x2="10" y2="10" />
        <line x1="14" y1="10" x2="22" y2="10" />
        <line x1="2" y1="14" x2="10" y2="14" />
        <line x1="14" y1="14" x2="22" y2="14" />
      </svg>
    ),
    calendar: () => (
      <svg
        className="w-4 h-4 mr-3"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
      >
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
        <line x1="16" y1="2" x2="16" y2="6" />
        <line x1="8" y1="2" x2="8" y2="6" />
        <line x1="3" y1="10" x2="21" y2="10" />
      </svg>
    ),
    phone: () => (
      <svg
        className="w-4 h-4 mr-3"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
      >
        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
      </svg>
    ),
    question: () => (
      <svg
        className="w-4 h-4 mr-3"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
      >
        <circle cx="12" cy="12" r="10" />
        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
        <line x1="12" y1="17" x2="12.01" y2="17" />
      </svg>
    ),
    login: () => (
      <svg
        className="w-4 h-4 mr-2"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
      >
        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
        <polyline points="10 17 15 12 10 7" />
        <line x1="15" y1="12" x2="3" y2="12" />
      </svg>
    ),
    burger: () => (
      <svg
        className="w-6 h-6"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
      >
        <line x1="3" y1="12" x2="21" y2="12" />
        <line x1="3" y1="6" x2="21" y2="6" />
        <line x1="3" y1="18" x2="21" y2="18" />
      </svg>
    ),
    close: () => (
      <svg
        className="w-6 h-6"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
      >
        <line x1="18" y1="6" x2="6" y2="18" />
        <line x1="6" y1="6" x2="18" y2="18" />
      </svg>
    ),
  };

  // While loading, return nothing to avoid flash of incorrect UI
  if (loadingUser) {
    return null;
  }

  return (
    <>
      <header
        className={`fixed top-0 left-0 w-full z-50 transition-all duration-300 ${
          !isHomePage || isScrolled
            ? "bg-black/95 backdrop-blur-sm shadow-lg"
            : "bg-transparent shadow-none"
        }`}
      >
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          {/* Mobile Layout */}
          <div className="flex items-center gap-2 md:hidden py-2">
            <a href="/" className="flex-shrink-0">
              <img
                className="h-12 w-auto object-contain"
                src="/Logo.png"
                alt="AllEvents Tickets"
              />
            </a>
            <form onSubmit={handleSearch} className="flex-1">
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                  <Icons.search />
                </div>
                <input
                  type="text"
                  value={searchQuery}
                  onChange={handleSearchChange}
                  onFocus={() => setIsSearchOpen(true)}
                  onBlur={closeSearchSoon}
                  className="block w-full pl-10 pr-3 py-2 border border-gray-700 rounded-lg bg-gray-800 text-gray-200 placeholder-gray-400 focus:bg-gray-700 focus:ring-1 focus:ring-blue-500 text-sm"
                  placeholder="Search..."
                />
                {isSearchOpen && (
                  <SearchSuggestions
                    query={searchQuery}
                    selectedCountry={selectedCountry}
                    onPick={() => setIsSearchOpen(false)}
                  />
                )}
              </div>
            </form>
            <button
              onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
              className="flex-shrink-0 p-2 rounded-md text-gray-300"
            >
              {isMobileMenuOpen ? <Icons.close /> : <Icons.burger />}
            </button>
          </div>

          {/* Desktop Layout */}
          <div className="hidden md:flex items-center justify-between h-16 md:h-20">
            <div className="flex items-center flex-shrink-0">
              <a href="/" className="flex items-center">
                <img
                  className="h-8 w-auto md:h-24"
                  src="/Logo.png"
                  alt="AllEvents Tickets"
                />
              </a>
              <div className="hidden md:block ml-3">
                <button
                  onClick={handleCityChange}
                  className="inline-flex items-center text-gray-300 hover:text-white text-sm font-medium px-2 py-1 rounded-md"
                >
                  <Icons.marker />
                  {selectedCountry?.name || "Select Country"}
                  <Icons.angleDown />
                </button>
              </div>
            </div>

            <div className="flex-1 flex justify-center mx-4">
              <form onSubmit={handleSearch} className="w-full max-w-xl">
                <div className="relative">
                  <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <Icons.search />
                  </div>
                  <input
                    type="text"
                    value={searchQuery}
                    onChange={handleSearchChange}
                    onFocus={() => setIsSearchOpen(true)}
                    onBlur={closeSearchSoon}
                    className="block w-full pl-10 pr-3 py-2 border border-gray-700 rounded-lg bg-gray-800 text-gray-200 placeholder-gray-400 focus:bg-gray-700 focus:ring-1 focus:ring-blue-500 text-sm"
                    placeholder="Search events..."
                  />
                  {isSearchOpen && (
                    <SearchSuggestions
                      query={searchQuery}
                      selectedCountry={selectedCountry}
                      onPick={() => setIsSearchOpen(false)}
                    />
                  )}
                </div>
              </form>
            </div>

            <div className="flex items-center space-x-6 flex-shrink-0">
              <button
                onClick={handleEvents}
                className="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-200 hover:text-white border-b-2 border-transparent hover:border-blue-500 transition-colors"
              >
                <Icons.ticket />
                Events
              </button>

              <Link
                to="/wishlist"
                className="relative inline-flex h-10 w-10 items-center justify-center rounded-full text-gray-200 transition-colors hover:bg-white/10 hover:text-white"
                aria-label={`Wishlist, ${wishlist.length} saved ${wishlist.length === 1 ? "event" : "events"}`}
              >
                <Heart size={21} />
                {wishlist.length > 0 && (
                  <span className="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#ff6b00] px-1 text-[10px] font-bold leading-none text-white">
                    {wishlist.length}
                  </span>
                )}
              </Link>

              {user ? (
                <div className="relative" ref={profileDropdownRef}>
                  <button
                    onClick={() =>
                      setIsProfileDropdownOpen(!isProfileDropdownOpen)
                    }
                    className="flex items-center space-x-2 focus:outline-none"
                  >
                    <img
                      src={getProfileImage()}
                      alt="User profile"
                      className="w-8 h-8 rounded-full object-cover border border-gray-600"
                    />
                    <span className="text-sm font-medium text-gray-200 hidden lg:inline-block">
                      {getUserDisplayName()}
                    </span>
                    <Icons.angleDown />
                  </button>

                  {isProfileDropdownOpen && (
                    <div className="absolute right-0 mt-2 w-52 bg-black border border-gray-700 rounded-lg shadow-lg py-1 z-10 transition-all duration-200 ease-out">
                      <button
                        onClick={handleBookingHistory}
                        className="flex w-full items-center gap-3 px-4 py-2 text-left text-sm text-gray-200 transition-colors hover:bg-gray-900"
                      >
                        <Icons.calendar />
                        Booking History
                      </button>
                      <hr className="border-gray-700 my-1" />
                      <button
                        onClick={handleLogout}
                        className="flex w-full items-center px-4 py-2 text-left text-sm text-red-400 transition-colors hover:bg-gray-900"
                      >
                        <svg
                          className="w-4 h-4 mr-3"
                          viewBox="0 0 24 24"
                          fill="none"
                          stroke="currentColor"
                          strokeWidth="2"
                        >
                          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                          <polyline points="16 17 21 12 16 7" />
                          <line x1="21" y1="12" x2="9" y2="12" />
                        </svg>
                        Logout
                      </button>
                    </div>
                  )}
                </div>
              ) : (
                <button
                  onClick={handleSignupClick}
                  className="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 hover:text-white transition-colors"
                >
                  <img
                    src="https://cdn2.allevents.in/transup/ab/ced64bf76d429aaa513418ff72f873/profile.png"
                    alt="User"
                    className="w-5 h-5 rounded-full mr-2 object-cover"
                  />
                  Sign in
                </button>
              )}

              <button
                onClick={handleBookNow}
                className="hidden lg:inline-flex items-center px-4 py-2 bg-white text-gray-900 text-sm font-semibold rounded-full hover:bg-gray-100 transition-colors shadow-md"
              >
                Book Now
              </button>
            </div>
          </div>
        </div>
      </header>

      {/* Mobile Menu Overlay */}
      {isMobileMenuOpen && (
        <div
          className="fixed inset-0 bg-black/70 z-40 md:hidden"
          onClick={() => setIsMobileMenuOpen(false)}
        />
      )}

      {/* Mobile Menu Panel */}
      <div
        ref={mobileMenuRef}
        className={`fixed top-0 left-0 h-full w-80 bg-black shadow-xl z-50 transform transition-transform duration-300 ease-in-out md:hidden ${
          isMobileMenuOpen ? "translate-x-0" : "-translate-x-full"
        }`}
      >
        <div className="pt-16 pb-6 px-5 h-full overflow-y-auto">
          <nav className="space-y-6">
            <div className="border-b border-gray-800 pb-4">
              <div className="flex items-center justify-between">
                <div className="flex items-center text-gray-200 font-medium">
                  <Icons.marker />
                  <span className="ml-2">
                    {selectedCountry?.name || "Select Country"}
                  </span>
                </div>
                <button
                  onClick={handleCityChange}
                  className="text-sm text-blue-400"
                >
                  Change
                </button>
              </div>
            </div>

            <button
              onClick={handleEvents}
              className="flex items-center w-full text-left text-gray-200 py-2 hover:bg-gray-900 px-3 rounded-lg"
            >
              <Icons.ticket />
              <span className="ml-2">Events</span>
            </button>

            <Link
              to="/wishlist"
              onClick={() => setIsMobileMenuOpen(false)}
              className="flex items-center justify-between w-full text-left text-gray-200 py-2 hover:bg-gray-900 px-3 rounded-lg"
            >
              <span className="flex items-center gap-2">
                <Heart size={18} />
                Wishlist
              </span>
              <span className="rounded-full bg-[#ff6b00] px-2 py-0.5 text-xs font-bold text-white">
                {wishlist.length}
              </span>
            </Link>

            <button
              onClick={handleBookingHistory}
              className="flex items-center w-full text-left text-gray-200 py-2 hover:bg-gray-900 px-3 rounded-lg"
            >
              <Icons.calendar />
              <span>Booking History</span>
            </button>

            <hr className="border-gray-800" />

            {user ? (
              <>
                <div className="flex items-center space-x-3 px-3 py-2 bg-gray-900 rounded-lg">
                  <img
                    src={getProfileImage()}
                    alt="Profile"
                    className="w-10 h-10 rounded-full object-cover"
                  />
                  <div className="flex-1">
                    <p className="text-sm font-medium text-gray-200">
                      {getUserDisplayName()}
                    </p>
                    <p className="text-xs text-gray-400 truncate">
                      {user.email}
                    </p>
                  </div>
                </div>
                <button
                  onClick={handleLogout}
                  className="flex items-center w-full text-left text-red-400 py-2 hover:bg-gray-900 px-3 rounded-lg"
                >
                  <svg
                    className="w-4 h-4 mr-2"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                  >
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <polyline points="16 17 21 12 16 7" />
                    <line x1="21" y1="12" x2="9" y2="12" />
                  </svg>
                  Logout
                </button>
              </>
            ) : (
              <button
                onClick={handleSignupClick}
                className="flex items-center w-full text-left text-gray-200 py-2 hover:bg-gray-900 px-3 rounded-lg"
              >
                <Icons.login />
                <span>Login / Sign up</span>
              </button>
            )}

            <hr className="border-gray-800" />

            <button
              onClick={handleAppDownload}
              className="flex items-center justify-between w-full text-left bg-gray-900 py-2 px-3 rounded-lg hover:bg-gray-800"
            >
              <div className="flex items-center text-gray-200">
                <Icons.phone />
                <span className="font-medium">Get the AllEventsApp</span>
              </div>
              <span className="bg-red-600 text-white text-xs px-1.5 py-0.5 rounded-full">
                New
              </span>
            </button>

            <button
              onClick={handleSupport}
              className="flex items-center w-full text-left text-gray-300 py-2 hover:bg-gray-900 px-3 rounded-lg"
            >
              <Icons.question />
              <span>Need help?</span>
            </button>

            <div className="pt-4">
              <button
                onClick={handleBookNow}
                className="w-full bg-white text-black font-semibold py-2 rounded-full hover:bg-gray-200 transition-colors"
              >
                Book Now
              </button>
            </div>
          </nav>
        </div>
      </div>

      {/* Custom Alert Modal */}
      {customAlert.show && (
        <div className="fixed inset-0 flex items-center justify-center z-50 bg-black/60 backdrop-blur-sm">
          <div className="bg-gray-900 rounded-xl shadow-xl max-w-sm w-full mx-4 border border-gray-700 transform transition-all duration-200 scale-100">
            <div className="p-5">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-semibold text-white">
                  Notification
                </h3>
                <button
                  onClick={closeAlert}
                  className="text-gray-400 hover:text-gray-200 transition-colors"
                >
                  <svg
                    className="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth="2"
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>
                </button>
              </div>
              <p className="text-gray-300 text-base mb-6">
                {customAlert.message}
              </p>
              <div className="flex justify-end">
                <button
                  onClick={closeAlert}
                  className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  OK
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Modals */}
      {isLocationModalOpen && (
        <LocationModal
          selectedCountry={selectedCountry}
          onCountrySelect={onCountryChange}
          onClose={() => setIsLocationModalOpen(false)}
        />
      )}
      {isSignupModalOpen && (
        <SignupModal onClose={() => setIsSignupModalOpen(false)} />
      )}
    </>
  );
};

export default Navbar;
