import React, { useState } from "react";

const moviesData = [
  {
    title: "Bhooth Bangla",
    language: "Hindi",
    certificate: "UA16+",
    type: ["New Releases"],
    format: ["2D"],
    rating: "7.5",
    image:
      "https://cdn.district.in/movies-assets/images/cinema/Bhooth-235a2dc0-3706-11f1-9c5b-6bf254b5f8b5.jpg",
  },
  {
    title: "Dhurandhar The Revenge",
    language: "Hindi",
    certificate: "UA13+",
    type: ["Re-Releases"],
    format: ["IMAX", "2D"],
    rating: "8.2",
    image:
      "https://cdn.district.in/movies-assets/images/cinema/DD-81414590-1d25-11f1-a5a3-f5a3fd184c9a.jpg",
  },
  {
    title: "KGF Chapter 2",
    language: "Hindi",
    certificate: "UA16+",
    type: ["New Releases"],
    format: ["4DX-2D"],
    rating: "8.4",
    image:
      "https://posterwa.com/cdn/shop/files/KGF1_1efddd90-f686-4e72-b6d7-10f976ad1c88.jpg?v=1693928797",
  },
  {
    title: "RRR",
    language: "Hindi",
    certificate: "UA13+",
    type: ["Re-Releases"],
    format: ["3D"],
    rating: "8.8",
    image:
      "https://m.media-amazon.com/images/M/MV5BNWMwODYyMjQtMTczMi00NTQ1LWFkYjItMGJhMWRkY2E3NDAyXkEyXkFqcGc@._V1_FMjpg_UX1000_.jpg",
  },
  {
    title: "Animal",
    language: "Hindi",
    certificate: "A",
    type: ["New Releases"],
    format: ["2D"],
    rating: "7.9",
    image:
      "https://upload.wikimedia.org/wikipedia/en/9/90/Animal_%282023_film%29_poster.jpg",
  },
  {
    title: "Salaar",
    language: "Hindi",
    certificate: "A",
    type: ["New Releases"],
    format: ["IMAX"],
    rating: "8.1",
    image:
      "https://mir-s3-cdn-cf.behance.net/project_modules/1400/24623b111384271.6000a082a190a.png",
  },
  {
    title: "Pushpa 2",
    language: "Hindi",
    certificate: "UA16+",
    type: ["Re-Releases"],
    format: ["2D"],
    rating: "8.3",
    image:
      "https://i.pinimg.com/1200x/31/b1/ea/31b1ea4c9e3d8302460b69f00e31c74a.jpg",
  },
  {
    title: "Brahmastra",
    language: "Hindi",
    certificate: "UA13+",
    type: ["New Releases"],
    format: ["3D"],
    rating: "7.2",
    image:
      "https://i0.wp.com/indiacurrents.com/wp-content/uploads/2022/09/bh2.jpeg",
  },
  {
    title: "Drishyam 2",
    language: "Hindi",
    certificate: "UA13+",
    type: ["Re-Releases"],
    format: ["2D"],
    rating: "8.7",
    image:
      "https://m.media-amazon.com/images/M/MV5BNGYyY2I5MzktMDg2MC00Nzc4LWIwNmYtMjg3NzE1ODQyMDllXkEyXkFqcGc@._V1_.jpg",
  },
  {
    title: "Leo",
    language: "Hindi",
    certificate: "A",
    type: ["New Releases"],
    format: ["IMAX"],
    rating: "7.8",
    image:
      "https://m.media-amazon.com/images/M/MV5BODhlNmFlMjAtNjM1OC00MGRiLThhNTAtNjA2NDQ0NWZhN2Y1XkEyXkFqcGc@._V1_.jpg",
  },
];

const filtersList = [
  "All",
  "Hindi",
  "New Releases",
  "Re-Releases",
  "3D",
  "4DX-2D",
  "IMAX",
];

const MoviesSection = () => {
  const [activeFilter, setActiveFilter] = useState("All");

  const filteredMovies =
    activeFilter === "All"
      ? moviesData
      : moviesData.filter(
          (movie) =>
            movie.language === activeFilter ||
            movie.type.includes(activeFilter) ||
            movie.format.includes(activeFilter),
        );

  return (
    <section className="py-10 bg-gray-100">
      <div className="max-w-[1200px] mx-auto px-4">
        {/* Heading */}
        <h2 className="text-2xl md:text-3xl font-bold mb-6">
          Only in Theatres
        </h2>

        {/* Filters */}
        <div className="flex flex-wrap gap-3 mb-8">
          {filtersList.map((filter) => (
            <button
              key={filter}
              onClick={() => setActiveFilter(filter)}
              className={`px-5 py-2 rounded-full text-sm font-medium transition border ${
                activeFilter === filter
                  ? "bg-[#ff6b00] text-white border-[#ff6b00] shadow-md"
                  : "bg-white text-gray-700 hover:bg-gray-200"
              }`}
            >
              {filter}
            </button>
          ))}
        </div>

        {/* Movie Grid */}
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5">
          {filteredMovies.map((movie, i) => (
            <div
              key={i}
              className="group bg-white rounded-xl shadow hover:shadow-xl transition overflow-hidden cursor-pointer"
            >
              <div className="relative">
                {/* Poster */}
                <img
                  src={movie.image}
                  alt={movie.title}
                  onError={(e) =>
                    (e.target.src =
                      "https://via.placeholder.com/300x450?text=No+Image")
                  }
                  className="w-full aspect-[2/3] object-cover"
                />

                {/* Gradient overlay */}
                <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition"></div>

                {/* Rating */}
                <span className="absolute top-2 left-2 bg-black/70 text-white text-xs px-2 py-1 rounded">
                  ⭐ {movie.rating}
                </span>

                {/* Book Button */}
                <button className="absolute bottom-3 left-1/2 -translate-x-1/2 bg-[#ff6b00] text-white px-4 py-1 text-sm rounded opacity-0 group-hover:opacity-100 transition">
                  Book Now
                </button>
              </div>

              <div className="p-3">
                <h3 className="font-semibold text-sm md:text-base line-clamp-1">
                  {movie.title}
                </h3>
                <p className="text-xs text-gray-500">
                  {movie.certificate} | {movie.language}
                </p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default MoviesSection;
