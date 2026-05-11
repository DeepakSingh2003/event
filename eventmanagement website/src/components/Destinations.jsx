import { useRef } from "react";
import { useNavigate } from "react-router-dom";
import { ChevronLeft, ChevronRight } from "lucide-react";

const API_BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";

// Indian destinations
const indiaDestinations = [
  {
    id: 1,
    name: "Mumbai",
    image:
      "https://images.unsplash.com/photo-1566552881560-0be862a7c445?auto=format&fit=crop&q=80&w=600",
  },
  {
    id: 2,
    name: "New Delhi",
    image:
      "https://images.unsplash.com/photo-1587474260584-136574528ed5?auto=format&fit=crop&q=80&w=600",
  },
  {
    id: 3,
    name: "Bengaluru",
    image:
      "https://www.shutterstock.com/image-photo/vidhana-soudha-front-viewbangalore-india-600nw-2465031059.jpg",
  },
  {
    id: 4,
    name: "Jaipur",
    image:
      "https://images.unsplash.com/photo-1599661046289-e31897846e41?auto=format&fit=crop&q=80&w=600",
  },
  {
    id: 5,
    name: "Kolkata",
    image:
      "https://s7ap1.scene7.com/is/image/incredibleindia/howrah-bridge-howrah-west-bengal-city-1-hero?qlt=82&ts=1742154305591",
  },
  {
    id: 6,
    name: "Hyderabad",
    image:
      "https://vj-prod-website-cms.s3.ap-southeast-1.amazonaws.com/depositphotos669042260xl-1734400332096.jpg",
  },
  {
    id: 7,
    name: "Ahmedabad",
    image:
      "https://t3.ftcdn.net/jpg/05/52/27/54/360_F_552275461_QdEaatYXQ1KGbAe08Xnh2bo8gp9Pmq5a.jpg",
  },
];

// International destinations (Dubai first, then others)
const internationalDestinations = [
  {
    id: 11, // changed order, but keep same id
    name: "Dubai",
    image:
      "https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&q=80&w=600",
  },
  {
    id: 8,
    name: "New York",
    image:
      "https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?auto=format&fit=crop&q=80&w=600",
  },
  {
    id: 9,
    name: "London",
    image:
      "https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?auto=format&fit=crop&q=80&w=600",
  },
  {
    id: 10,
    name: "Paris",
    image:
      "https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&q=80&w=600",
  },
  {
    id: 12,
    name: "Tokyo",
    image:
      "https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?auto=format&fit=crop&q=80&w=600",
  },
  {
    id: 13,
    name: "Sydney",
    image:
      "https://images.unsplash.com/photo-1506973035872-a4ec16b8e8d9?auto=format&fit=crop&q=80&w=600",
  },
];

// Combine: International first, then India
const destinations = [...internationalDestinations, ...indiaDestinations];

const slugify = (value) =>
  value
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-|-$/g, "");

const IndiaDestinationsSlider = () => {
  const navigate = useNavigate();
  const scrollRef = useRef(null);

  const handleDestinationClick = async (city) => {
    try {
      const response = await fetch(
        `${API_BASE_URL}/api/cities?search=${encodeURIComponent(city.name)}`,
        {
          headers: { Accept: "application/json" },
        },
      );

      if (response.ok) {
        const result = await response.json();
        const matchedCity = result.data?.find(
          (item) => item.name?.toLowerCase() === city.name.toLowerCase(),
        );

        if (matchedCity) {
          navigate(`/city/${matchedCity.slug || matchedCity.id}`);
          return;
        }
      }
    } catch (error) {
      console.error("Unable to find destination city:", error);
    }

    navigate(`/city/${slugify(city.name)}`);
  };

  const scroll = (direction) => {
    if (scrollRef.current) {
      const { scrollLeft, clientWidth } = scrollRef.current;
      const scrollAmount = clientWidth * 0.8;
      const scrollTo =
        direction === "left"
          ? scrollLeft - scrollAmount
          : scrollLeft + scrollAmount;

      scrollRef.current.scrollTo({ left: scrollTo, behavior: "smooth" });
    }
  };

  return (
    <div className="bg-white p-8 md:p-12 overflow-hidden">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <div className="flex justify-between items-center mb-10">
          <h2 className="text-[18px] md:text-3xl font-bold text-gray-900">
            Explore top destinations in India & Worldwide
          </h2>

          <div className="flex gap-4">
            <button
              onClick={() => scroll("left")}
              className="p-3 rounded-full border border-gray-200 hover:bg-orange-50 hover:border-orange-200 transition shadow-sm active:scale-95 group"
            >
              <ChevronLeft
                size={24}
                className="text-gray-600 group-hover:text-[#ff6b00]"
              />
            </button>

            <button
              onClick={() => scroll("right")}
              className="p-3 rounded-full border border-gray-200 hover:bg-orange-50 hover:border-orange-200 transition shadow-sm active:scale-95 group"
            >
              <ChevronRight
                size={24}
                className="text-gray-600 group-hover:text-[#ff6b00]"
              />
            </button>
          </div>
        </div>

        {/* Slider - shows international first (Dubai is first), then Indian cities */}
        <div
          ref={scrollRef}
          className="flex gap-6 overflow-x-auto no-scrollbar scroll-smooth snap-x pb-8"
        >
          {destinations.map((city) => (
            <div
              key={city.id}
              onClick={() => handleDestinationClick(city)}
              className="min-w-[280px] md:min-w-[360px] snap-start flex flex-col group cursor-pointer"
            >
              {/* Image */}
              <div className="relative h-56 md:h-64 rounded-t-[42px] overflow-hidden bg-orange-50">
                <img
                  src={city.image}
                  alt={city.name}
                  className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                />

                {/* Gradient overlay */}
                <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent" />

                {/* Title */}
                <div className="absolute bottom-6 left-6">
                  <h3 className="text-white text-2xl md:text-3xl font-bold">
                    {city.name}
                  </h3>
                </div>
              </div>

              {/* Orange Accent Bar */}
              <div className="h-2 w-full bg-[#ff6b00] rounded-b-full shadow-[0_4px_12px_rgba(255,107,0,0.4)] group-hover:h-3 transition-all" />
            </div>
          ))}
        </div>
      </div>

      {/* Hide scrollbar styling */}
      <style jsx>{`
        .no-scrollbar::-webkit-scrollbar {
          display: none;
        }
        .no-scrollbar {
          -ms-overflow-style: none;
          scrollbar-width: none;
        }
      `}</style>
    </div>
  );
};

export default IndiaDestinationsSlider;
