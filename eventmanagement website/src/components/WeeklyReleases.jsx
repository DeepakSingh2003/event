import React from "react";

const weeklyMovies = [
  {
    title: "The Devil Wears Prada 2",
    language: "English",
    certificate: "A",
    rating: "8.1",
    image:
      "https://cdn.district.in/movies-assets/images/cinema/The-Devil-Wears-Prada-2_Poster-5b0750c0-1ec6-11f1-89a4-1dbb292c5cd4-ae8fa0d0-3fcd-11f1-afb5-fba0ff94902c.jpg",
  },
  {
    title: "Kara",
    language: "Tamil",
    certificate: "UA16+",
    rating: "7.5",
    image:
      "https://cdn.district.in/movies-assets/images/cinema/Kara-e0647600-1870-11f1-8620-a7b248408a4f.jpg",
  },
  {
    title: "Raja Shivaji",
    language: "Marathi",
    certificate: "UA16+",
    rating: "8.0",
    image:
      "https://cdn.district.in/movies-assets/images/cinema/Raja-Shivaji-6393c530-0eee-11f1-be2f-4531116c6521-13c53a60-41fd-11f1-ad0a-17fec178475d.jpg",
  },
  {
    title: "Ek Din",
    language: "Hindi",
    certificate: "UA13+",
    rating: "7.2",
    image:
      "https://cdn.district.in/movies-assets/images/cinema/ek-din-72476d30-f2c8-11f0-86e5-8d1b0376dd06-a62f6d00-2757-11f1-95f7-b5d47678c833.jpg",
  },
];

const WeeklyReleases = () => {
  return (
    <section className="py-10 bg-gray-100">
      <div className="max-w-[1200px] mx-auto px-4">
        {/* Heading */}
        <h2 className="text-2xl md:text-3xl font-bold mb-6">
          This Week’s Releases
        </h2>

        {/* GRID */}
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-5">
          {weeklyMovies.map((movie, i) => (
            <div
              key={i}
              className="group bg-white rounded-xl shadow hover:shadow-xl transition overflow-hidden cursor-pointer"
            >
              <div className="relative">
                {/* Poster (HEIGHT REDUCED HERE) */}
                <img
                  src={movie.image}
                  alt={movie.title}
                  onError={(e) =>
                    (e.target.src =
                      "https://via.placeholder.com/300x450?text=No+Image")
                  }
                  className="w-full aspect-[3/4] object-cover"
                />

                {/* Gradient Hover */}
                <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition"></div>

                {/* Rating */}
                <span className="absolute top-2 left-2 bg-black/70 text-white text-xs px-2 py-1 rounded">
                  ⭐ {movie.rating}
                </span>

                {/* Button */}
                <button className="absolute bottom-3 left-1/2 -translate-x-1/2 bg-[#ff6b00] text-white px-4 py-1 text-sm rounded opacity-0 group-hover:opacity-100 transition">
                  Book Now
                </button>
              </div>

              {/* TEXT (slightly reduced padding) */}
              <div className="p-2">
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

export default WeeklyReleases;
