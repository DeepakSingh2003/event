import { useState, useEffect, useRef } from "react";

const API_BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";

const LocationModal = ({ selectedCountry, onCountrySelect, onClose }) => {
  const [searchTerm, setSearchTerm] = useState("");
  const [countries, setCountries] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const modalRef = useRef(null);

  const scrollingCities = [
    "London",
    "Orlando",
    "Vancouver",
    "Miami",
    "Bangalore",
    "Edinburgh",
    "Washington",
    "Delhi",
    "Los Angeles",
    "Ahmedabad",
    "New York",
    "Mumbai",
    "your city",
  ];

  useEffect(() => {
    const handleClickOutside = (e) => {
      if (modalRef.current && !modalRef.current.contains(e.target)) onClose();
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, [onClose]);

  useEffect(() => {
    const fetchCountries = async () => {
      try {
        const response = await fetch(`${API_BASE_URL}/api/countries`, {
          headers: {
            Accept: "application/json",
          },
        });

        if (!response.ok) {
          setError("Unable to load countries right now.");
          return;
        }

        const result = await response.json();
        setCountries(Array.isArray(result?.data) ? result.data : []);
      } catch (fetchError) {
        console.error("Error fetching countries:", fetchError);
        setError("Unable to load countries right now.");
      } finally {
        setLoading(false);
      }
    };

    fetchCountries();
  }, []);

  const filteredCountries = countries.filter((country) =>
    country.name.toLowerCase().includes(searchTerm.trim().toLowerCase()),
  );

  const handleCountryClick = (country) => {
    onCountrySelect?.(country);
    onClose();
  };

  const handleAllCountriesClick = () => {
    onCountrySelect?.(null);
    onClose();
  };

  return (
    <div className="fixed inset-0 bg-black/60 z-[1040] flex justify-center items-center p-4">
      <div
        ref={modalRef}
        className="w-full max-w-[760px] bg-white rounded-xl shadow-2xl relative overflow-hidden flex flex-col"
      >
        {/* CLOSE BUTTON */}
        <button
          onClick={onClose}
          className="absolute top-3 right-4 text-white/80 text-2xl hover:text-white z-20"
        >
          &times;
        </button>

        {/* HEADER */}
        <div className="p-6 md:p-8 bg-gradient-to-r from-[#2196f3] to-[#00bcd4] text-white text-center">
          <h1 className="text-xl md:text-2xl font-bold flex flex-wrap justify-center items-center gap-2 mb-6">
            AllEvents in
            <div className="h-[32px] overflow-hidden relative min-w-[120px] border-b border-white/40">
              <div className="animate-city-scroll">
                {scrollingCities.map((city, i) => (
                  <div
                    key={i}
                    className="h-[32px] flex items-center justify-center font-medium italic text-lg md:text-xl"
                  >
                    {city}
                  </div>
                ))}
              </div>
            </div>
          </h1>

          {/* SEARCH BAR - Scaled Down */}
          <div className="w-full max-w-lg mx-auto bg-white rounded-md flex items-center shadow-md h-11 overflow-hidden">
            <div className="pl-3">
              <svg
                className="w-4 h-4 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth="2.5"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
              </svg>
            </div>
            <input
              type="text"
              className="flex-1 h-full px-2 outline-none text-gray-700 text-sm placeholder:text-gray-400"
              placeholder="Search country..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
            <div className="h-6 w-[1px] bg-gray-200"></div>
            <button className="px-4 flex items-center gap-1.5 text-[#0A74BF] font-bold hover:bg-gray-50 h-full transition-colors text-xs uppercase tracking-wider">
              <svg width="14" height="14" viewBox="0 0 13 12" fill="none">
                <path
                  d="M10.4192 6.05942C10.4192 8.4453 8.48509 10.3794 6.09922 10.3794M10.4192 6.05942C10.4192 3.67355 8.48509 1.73942 6.09922 1.73942M10.4192 6.05942H11.4992M6.09922 10.3794C3.71335 10.3794 1.77922 8.4453 1.77922 6.05942M6.09922 10.3794V11.4594M1.77922 6.05942C1.77922 3.67355 3.71335 1.73942 6.09922 1.73942M1.77922 6.05942H0.699219M6.09922 1.73942V0.659424M7.71922 6.05942C7.71922 6.95413 6.99392 7.67942 6.09922 7.67942C5.20452 7.67942 4.47922 6.95413 4.47922 6.05942C4.47922 5.16472 5.20452 4.43942 6.09922 4.43942C6.99392 4.43942 7.71922 5.16472 7.71922 6.05942Z"
                  stroke="currentColor"
                  strokeWidth="1.2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                />
              </svg>
              Current Location
            </button>
          </div>
        </div>

        {/* BODY */}
        <div className="p-6 md:px-10 bg-white">
          <div className="mb-6">
            <h3 className="text-base font-bold text-gray-800">
              Select Country
            </h3>
            <p className="text-gray-400 text-[12px] mt-0.5">
              Explore Cities Near You will update from backend automatically
            </p>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-8">
            <div
              onClick={handleAllCountriesClick}
              className="flex items-center group cursor-pointer"
            >
              <div className="w-10 h-10 flex items-center justify-center bg-[#f5f5f5] rounded-lg mr-3 group-hover:bg-blue-50 transition-colors shrink-0">
                <svg
                  className="w-5 h-5 text-gray-400 group-hover:text-[#0A74BF]"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                  viewBox="0 0 24 24"
                >
                  <path d="M2 12H22M2 12C2 17.5228 6.47715 22 12 22M2 12C2 6.47715 6.47715 2 12 2M22 12C22 17.5228 17.5228 22 12 22M22 12C22 6.47715 17.5228 2 12 2" />
                </svg>
              </div>
              <div className="flex flex-col min-w-0">
                <span className="font-bold text-gray-700 group-hover:text-[#0A74BF] text-sm truncate">
                  All Countries
                </span>
                <span className="text-[11px] text-gray-400 font-medium">
                  Show all cities
                </span>
              </div>
            </div>

            {loading && (
              <p className="text-sm text-gray-400 col-span-full">
                Loading countries...
              </p>
            )}

            {error && !loading && (
              <p className="text-sm text-red-500 col-span-full">{error}</p>
            )}

            {!loading &&
              !error &&
              filteredCountries.map((country) => (
                <div
                  key={country.id}
                  onClick={() => handleCountryClick(country)}
                  className="flex items-center group cursor-pointer"
                >
                  <div
                    className={`w-10 h-10 flex items-center justify-center rounded-lg mr-3 transition-colors shrink-0 ${
                      selectedCountry?.id === country.id
                        ? "bg-blue-50"
                        : "bg-[#f5f5f5] group-hover:bg-blue-50"
                    }`}
                  >
                    <svg
                      className={`w-5 h-5 ${
                        selectedCountry?.id === country.id
                          ? "text-[#0A74BF]"
                          : "text-gray-400 group-hover:text-[#0A74BF]"
                      }`}
                      fill="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                    </svg>
                  </div>
                  <div className="flex flex-col min-w-0">
                    <span className="font-bold text-gray-700 group-hover:text-[#0A74BF] text-sm truncate">
                      {country.name}
                    </span>
                    <span className="text-[11px] text-gray-400 font-medium">
                      {country.cities_count}+ Cities
                    </span>
                  </div>
                </div>
              ))}

            {!loading && !error && filteredCountries.length === 0 && (
              <p className="text-sm text-gray-400 col-span-full">
                No countries found.
              </p>
            )}
          </div>
        </div>
      </div>

      <style jsx>{`
        @keyframes city-scroll {
          0%,
          10% {
            transform: translateY(0);
          }
          15%,
          25% {
            transform: translateY(-32px);
          }
          30%,
          40% {
            transform: translateY(-64px);
          }
          45%,
          55% {
            transform: translateY(-96px);
          }
          60%,
          70% {
            transform: translateY(-128px);
          }
          75%,
          85% {
            transform: translateY(-160px);
          }
          90%,
          100% {
            transform: translateY(-192px);
          }
        }
        .animate-city-scroll {
          animation: city-scroll 18s infinite ease-in-out;
        }
      `}</style>
    </div>
  );
};

export default LocationModal;
