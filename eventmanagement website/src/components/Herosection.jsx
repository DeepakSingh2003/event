import { useState } from "react";
import LocationModal from "./LocationModal";
import SearchSuggestions from "./SearchSuggestions";

const HeroSection = ({ selectedCountry, onCountryChange }) => {
  const [isSearchOpen, setIsSearchOpen] = useState(false);
  const [isLocationModalOpen, setIsLocationModalOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");

  const handleSubmit = (event) => {
    event.preventDefault();
    setIsSearchOpen(true);
  };

  const handleSearchChange = (event) => {
    setSearchQuery(event.target.value);
    setIsSearchOpen(true);
  };

  const closeSearchSoon = () => {
    window.setTimeout(() => setIsSearchOpen(false), 120);
  };

  return (
    <>
      <section className="relative min-h-[600px] w-full flex items-center justify-center text-white px-4 overflow-hidden">
        {/* Background video (events only) */}
        <video
          autoPlay
          loop
          muted
          playsInline
          className="absolute inset-0 w-full h-full object-cover z-0"
        >
          <source src="/12525522-uhd_3840_2160_60fps.mp4" type="video/mp4" />
        </video>

        {/* Overlay for better readability */}
        <div className="absolute inset-0 bg-black/40 z-10"></div>

        {/* Content */}
        <div className="relative z-20 w-full max-w-5xl mx-auto flex flex-col items-center text-center mt-16">
          <h1 className="text-2xl sm:text-3xl md:text-5xl font-bold leading-tight mb-4">
            Book Event Tickets Instantly
          </h1>

          <p className="text-sm sm:text-base md:text-lg text-gray-200 mb-8 max-w-2xl">
            Discover live events, concerts, shows, and experiences near you.
            Reserve your seats in seconds with a seamless booking experience.
          </p>

          {/* Search Bar */}
          <form
            onSubmit={handleSubmit}
            className="w-full max-w-4xl mx-auto relative"
          >
            <div className="flex items-center bg-white/90 backdrop-blur-md rounded-xl shadow-lg h-14 overflow-hidden border border-gray-200">
              {/* Left Search */}
              <div className="flex items-center flex-1 px-4">
                <svg
                  className="w-5 h-5 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                  />
                </svg>

                <input
                  type="text"
                  placeholder="Search Events, Categories, Location..."
                  value={searchQuery}
                  onChange={handleSearchChange}
                  onFocus={() => setIsSearchOpen(true)}
                  onBlur={closeSearchSoon}
                  className="w-full px-3 py-2 text-gray-700 outline-none bg-transparent text-sm md:text-base placeholder:text-gray-500"
                />
              </div>

              {/* Divider */}
              <div className="h-6 w-[1px] bg-gray-300"></div>

              {/* Location */}
              <div
                onClick={() => setIsLocationModalOpen(true)}
                className="flex items-center px-4 cursor-pointer hover:bg-gray-100 transition h-full"
              >
                <svg
                  className="w-4 h-4 text-gray-500"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="2"
                    d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"
                  />
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                </svg>

                <span className="ml-2 text-gray-700 font-medium text-sm md:text-base">
                  {selectedCountry?.name || "All Countries"}
                </span>

                <svg
                  className="w-4 h-4 ml-1 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="2"
                    d="M19 9l-7 7-7-7"
                  />
                </svg>
              </div>
            </div>
            {isSearchOpen && (
              <SearchSuggestions
                query={searchQuery}
                selectedCountry={selectedCountry}
                onPick={() => setIsSearchOpen(false)}
              />
            )}
          </form>

          {/* CTA Button - only Browse Events */}
          <div className="mt-8 flex justify-center">
            <button className="px-6 py-3 rounded-lg font-semibold transition bg-[#ff6b00] text-white">
              Browse Events
            </button>
          </div>

          {/* Categories - only event-related */}
          <div className="mt-6 flex flex-wrap justify-center gap-3 text-sm">
            {[
              "Concerts",
              "Comedy Shows",
              "Live Events",
              "Workshops",
              "Festivals",
            ].map((cat) => (
              <span
                key={cat}
                className="px-4 py-1 bg-white/10 hover:bg-white/20 rounded-full cursor-pointer transition"
              >
                {cat}
              </span>
            ))}
          </div>
        </div>
      </section>
      {isLocationModalOpen && (
        <LocationModal
          selectedCountry={selectedCountry}
          onCountrySelect={onCountryChange}
          onClose={() => setIsLocationModalOpen(false)}
        />
      )}
    </>
  );
};

export default HeroSection;
