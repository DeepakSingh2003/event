import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { X } from "lucide-react";

const API_BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";

const CityCard = ({ city, international = false, onSelect }) => (
  <div
    onClick={() => onSelect(city, international)}
    className="flex items-center gap-3 p-4 bg-white border border-gray-200 rounded-xl hover:shadow-md transition group min-w-[200px] snap-start cursor-pointer"
  >
    <div className="w-11 h-11 flex items-center justify-center bg-orange-50 rounded-lg shrink-0 transition-colors group-hover:bg-orange-100">
      <svg
        className="w-6 h-6 text-[#ff6b00]"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
        viewBox="0 0 24 24"
      >
        <path
          strokeLinecap="round"
          strokeLinejoin="round"
          d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"
        />
        <path
          strokeLinecap="round"
          strokeLinejoin="round"
          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
        />
      </svg>
    </div>

    <div className="flex flex-col min-w-0">
      <span className="text-[15px] font-semibold text-gray-800 group-hover:text-[#ff6b00] transition">
        {city.name}
      </span>
      <span className="text-[12px] text-gray-500 font-medium">
        {city.eventsCount}+ Events
      </span>
    </div>
  </div>
);

const SearchTile = ({ label, subLabel, onClick }) => (
  <button
    type="button"
    onClick={onClick}
    className="flex items-center gap-3 p-4 bg-white border border-dashed border-gray-300 rounded-xl hover:shadow-md transition group text-left min-w-[200px] snap-start"
  >
    <div className="w-11 h-11 flex items-center justify-center bg-orange-50 rounded-lg shrink-0 group-hover:bg-orange-100">
      <svg
        className="w-6 h-6 text-[#ff6b00]"
        fill="none"
        stroke="currentColor"
        strokeWidth="2.5"
        viewBox="0 0 24 24"
      >
        <path
          strokeLinecap="round"
          strokeLinejoin="round"
          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
        />
      </svg>
    </div>
    <div className="flex flex-col">
      <span className="text-[15px] font-semibold text-gray-800 group-hover:text-[#ff6b00]">
        {label}
      </span>
      <span className="text-[12px] text-gray-500">{subLabel}</span>
    </div>
  </button>
);

const ExploreCities = ({ selectedCountry, searchQuery = "" }) => {
  const navigate = useNavigate();

  const [cities, setCities] = useState([]);
  const [internationalCities, setInternationalCities] = useState([]);
  const [cityModal, setCityModal] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      setError("");

      try {
        const params = new URLSearchParams();

        if (selectedCountry?.id) {
          params.set("country_id", selectedCountry.id);
        }

        if (searchQuery.trim()) {
          params.set("search", searchQuery.trim());
        }

        const query = params.toString();
        const citiesRes = await fetch(
          `${API_BASE_URL}/api/cities${query ? `?${query}` : ""}`,
          {
            method: "GET",
            headers: {
              Accept: "application/json",
            },
          },
        );

        if (!citiesRes.ok) {
          console.log("Cities API status:", citiesRes.status);
          setError("Unable to load cities right now.");
          return;
        }

        const citiesJson = await citiesRes.json();
        const cityList = Array.isArray(citiesJson?.data) ? citiesJson.data : [];

        const normalizedCities = cityList.map((city) => ({
          id: city.id ?? city.slug ?? city.name,
          name: city.name,
          slug: city.slug,
          eventsCount: city.events_count ?? city.eventsCount ?? 0,
          countryName:
            city.country_name ?? city.country ?? selectedCountry?.name ?? "",
        }));

        if (selectedCountry || searchQuery.trim()) {
          setCities(normalizedCities);
          setInternationalCities([]);
          return;
        }

        setCities(
          normalizedCities.filter(
            (city) => city.countryName.trim().toLowerCase() === "india",
          ),
        );
        setInternationalCities(
          normalizedCities.filter(
            (city) =>
              city.countryName &&
              city.countryName.trim().toLowerCase() !== "india",
          ),
        );
      } catch (fetchError) {
        console.error("Error fetching cities:", fetchError);
        setError("Unable to load cities right now.");
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [selectedCountry, searchQuery]);

  const heading = selectedCountry
    ? `Explore Cities in ${selectedCountry.name}`
    : searchQuery.trim()
      ? `Cities matching "${searchQuery.trim()}"`
      : "Explore Cities near you";

  const handleCitySelect = (city) => {
    setCityModal(null);
    navigate(`/city/${city.slug || city.id}`);
  };

  const visibleCities = cities.slice(0, 7);
  const visibleInternationalCities = internationalCities.slice(0, 7);

  const openCityModal = (title, cityList) => {
    setCityModal({ title, cities: cityList });
  };

  return (
    <section className="py-8 bg-[#f9f9f9]">
      <div className="max-w-[1170px] mx-auto px-4">
        <div className="flex items-center justify-between mb-5">
          <h2 className="text-[22px] md:text-[26px] font-bold text-[#222]">
            {heading}
          </h2>
        </div>

        {loading && (
          <p className="text-gray-500 font-medium">Loading cities...</p>
        )}
        {error && !loading && <p className="text-red-600 font-medium">{error}</p>}
        {!loading && !error && cities.length === 0 && (
          <p className="text-gray-500 font-medium">
            No cities found for this selection.
          </p>
        )}

        <div className="flex overflow-x-auto gap-4 pb-4 md:grid md:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 md:gap-4 md:overflow-visible snap-x snap-mandatory scrollbar-hide">
          {!loading &&
            !error &&
            visibleCities.map((city, index) => (
              <CityCard
                key={city.id || index}
                city={city}
                onSelect={handleCitySelect}
              />
            ))}

          {!loading && !error && (
            <SearchTile
              label="Explore All"
              subLabel={`${cities.length}+ cities`}
              onClick={() => openCityModal(heading, cities)}
            />
          )}
        </div>

        {internationalCities.length > 0 && (
          <>
            <div className="flex items-center justify-between mt-12 mb-5">
              <h2 className="text-[22px] md:text-[26px] font-bold text-[#222]">
                Explore International Events
              </h2>
            </div>

            <div className="flex overflow-x-auto gap-4 pb-4 md:grid md:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 md:gap-4 md:overflow-visible snap-x snap-mandatory scrollbar-hide">
              {visibleInternationalCities.map((city, index) => (
                <CityCard
                  key={city.id || index}
                  city={city}
                  international
                  onSelect={handleCitySelect}
                />
              ))}

              <SearchTile
                label="Explore All"
                subLabel={`${internationalCities.length}+ cities`}
                onClick={() =>
                  openCityModal("Explore International Events", internationalCities)
                }
              />
            </div>
          </>
        )}
      </div>

      {cityModal && (
        <div className="fixed inset-0 z-[90] flex items-center justify-center bg-black/60 px-4">
          <div className="w-full max-w-3xl rounded-2xl bg-white shadow-2xl">
            <div className="flex items-center justify-between border-b border-gray-100 px-5 py-4">
              <div>
                <h3 className="text-xl font-bold text-gray-900">
                  {cityModal.title}
                </h3>
                <p className="text-sm text-gray-500">
                  Choose a city to view events.
                </p>
              </div>
              <button
                type="button"
                onClick={() => setCityModal(null)}
                className="rounded-full p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900"
              >
                <X size={22} />
              </button>
            </div>

            <div className="max-h-[70vh] overflow-y-auto p-5">
              <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
                {cityModal.cities.map((city, index) => (
                  <button
                    key={city.id || index}
                    type="button"
                    onClick={() => handleCitySelect(city)}
                    className="rounded-xl border border-gray-100 bg-gray-50 p-4 text-left transition hover:border-orange-200 hover:bg-orange-50"
                  >
                    <span className="block font-bold text-gray-900">
                      {city.name}
                    </span>
                    <span className="mt-1 block text-sm text-gray-500">
                      {city.eventsCount}+ Events
                    </span>
                  </button>
                ))}
              </div>
            </div>
          </div>
        </div>
      )}

      <style>{`
        .scrollbar-hide::-webkit-scrollbar {
          display: none;
        }
        .scrollbar-hide {
          -ms-overflow-style: none;
          scrollbar-width: none;
        }
      `}</style>
    </section>
  );
};

export default ExploreCities;
