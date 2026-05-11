import { useState } from "react";
import { Routes, Route } from "react-router-dom";
import Home from "./pages/Home";
import CityPage from "./pages/CityPage";
import EventDetails from "./pages/EventDetails";
import BookingPage from "./pages/BookingPage";
import SearchResults from "./pages/SearchResults";
import AboutPage from "./pages/AboutPage";
import CareersPage from "./pages/CareersPage";
import ContactPage from "./pages/ContactPage";
import HelpSupportPage from "./pages/HelpSupportPage";
import MyBookingsPage from "./pages/MyBookingsPage";
import NotificationsPage from "./pages/NotificationsPage";
import PrivacyPolicyPage from "./pages/PrivacyPolicyPage";
import TermsPage from "./pages/TermsPage";
import WishlistPage from "./pages/WishlistPage";

import Navbar from "./components/Navbar";
import Footer from "./components/Footer";

function App() {
  const [selectedCountry, setSelectedCountry] = useState(null);

  return (
    <>
      <Navbar
        selectedCountry={selectedCountry}
        onCountryChange={setSelectedCountry}
      />

      <Routes>
        <Route
          path="/"
          element={
            <Home
              selectedCountry={selectedCountry}
              onCountryChange={setSelectedCountry}
            />
          }
        />
        <Route path="/city/:cityName" element={<CityPage />} />
        <Route path="/international/:cityName" element={<CityPage />} />
        <Route path="/event/:id" element={<EventDetails />} />
        <Route path="/booking/:showId" element={<BookingPage />} />
        <Route path="/search" element={<SearchResults />} />
        <Route path="/my-bookings" element={<MyBookingsPage />} />
        <Route path="/wishlist" element={<WishlistPage />} />
        <Route path="/notifications" element={<NotificationsPage />} />
        <Route path="/help" element={<HelpSupportPage />} />
        <Route path="/about" element={<AboutPage />} />
        <Route path="/careers" element={<CareersPage />} />
        <Route path="/contact" element={<ContactPage />} />
        <Route path="/privacy" element={<PrivacyPolicyPage />} />
        <Route path="/terms" element={<TermsPage />} />
      </Routes>

      <Footer />
    </>
  );
}

export default App;
