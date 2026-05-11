import HeroSection from "../components/Herosection";
import ExploreCities from "../components/ExploreCities";
import MostLovedCategories from "../components/MostLovedCategories";
import TrendingEvents from "../components/TrendingEvents";

import PopularEvents from "../components/PopularEvents";
import Destination from "../components/Destinations";
import TestimonialWall from "../components/TestimonialWall";
import HomeBanner from "../components/HomeBanner";
import InternationalEvents from "../components/InternationalEvents";

const Home = ({ selectedCountry, onCountryChange }) => {
  return (
    <>
      <HeroSection
        selectedCountry={selectedCountry}
        onCountryChange={onCountryChange}
      />
      <ExploreCities selectedCountry={selectedCountry} />
      <MostLovedCategories />
      <TrendingEvents />
      <InternationalEvents />

      <PopularEvents />
      <Destination />
      <TestimonialWall />
      <HomeBanner />
    </>
  );
};

export default Home;
