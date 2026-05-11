import React from "react";

const HomeBanner = () => {
  return (
    <section className="relative w-full overflow-hidden bg-gradient-to-r from-[#1f4f8b] to-[#1e88a8] text-white py-16 px-6">
      {/* Floating circles */}
      <div className="absolute top-6 left-1/3 w-4 h-4 bg-white/20 rounded-full"></div>
      <div className="absolute bottom-6 left-1/2 w-12 h-12 bg-white/10 rounded-full"></div>
      <div className="absolute top-10 right-10 w-6 h-6 bg-white/20 rounded-full"></div>

      {/* CENTER CONTENT */}
      <div className="max-w-3xl mx-auto text-center">
        <h1 className="text-2xl md:text-4xl font-bold mb-3">
          Discover & Book Amazing Events Near You
        </h1>

        <p className="text-white/80 mb-5 text-sm md:text-base">
          From concerts and parties to workshops and festivals — find your next
          experience and book instantly.
        </p>

        <div className="flex flex-col sm:flex-row justify-center gap-3">
          <button className="bg-white text-[#1f4f8b] px-5 py-2.5 rounded-lg font-semibold">
            Explore Events
          </button>

          <button className="border border-white/40 px-5 py-2.5 rounded-lg font-semibold">
            List Your Event
          </button>
        </div>
      </div>
    </section>
  );
};

export default HomeBanner;
