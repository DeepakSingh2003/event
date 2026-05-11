import { Link } from "react-router-dom";
import { FaFacebookF, FaInstagram, FaYoutube } from "react-icons/fa";
import { FaXTwitter } from "react-icons/fa6";

const eventLinks = [
  { label: "Concerts", to: "/search?q=Concerts&label=Concerts" },
  { label: "Comedy Shows", to: "/search?q=Comedy&label=Comedy" },
  { label: "Workshops", to: "/search?q=Workshops&label=Workshops" },
  { label: "Festivals", to: "/search?q=Festivals&label=Festivals" },
  { label: "Live Events", to: "/search" },
];

const accountLinks = [
  { label: "My Bookings", to: "/my-bookings" },
  { label: "Wishlist", to: "/wishlist" },
  { label: "Notifications", to: "/notifications" },
  { label: "Help & Support", to: "/help" },
];

const companyLinks = [
  { label: "About Us", to: "/about" },
  { label: "Careers", to: "/careers" },
  { label: "Contact", to: "/contact" },
  { label: "Privacy Policy", to: "/privacy" },
  { label: "Terms & Conditions", to: "/terms" },
];

const popularCities = [
  { label: "Delhi", to: "/city/new-delhi" },
  { label: "Mumbai", to: "/city/mumbai" },
  { label: "Bangalore", to: "/city/bengaluru" },
  { label: "Hyderabad", to: "/city/hyderabad" },
  { label: "Ahmedabad", to: "/city/ahmedabad" },
  { label: "Chennai", to: "/city/chennai" },
  { label: "Pune", to: "/city/pune" },
  { label: "Kolkata", to: "/city/kolkata" },
  { label: "Jaipur", to: "/city/jaipur" },
];

const socialLinks = [
  { label: "Facebook", href: "https://www.facebook.com", Icon: FaFacebookF },
  { label: "X", href: "https://x.com", Icon: FaXTwitter },
  { label: "Instagram", href: "https://www.instagram.com", Icon: FaInstagram },
  { label: "YouTube", href: "https://www.youtube.com", Icon: FaYoutube },
];

const FooterColumn = ({ title, links }) => (
  <div>
    <h3 className="text-white font-semibold mb-4">{title}</h3>
    <ul className="space-y-2 text-sm">
      {links.map((link) => (
        <li key={link.label}>
          <Link to={link.to} className="hover:text-[#ff6b00] transition">
            {link.label}
          </Link>
        </li>
      ))}
    </ul>
  </div>
);

const Footer = () => {
  return (
    <footer className="bg-[#1c1f26] text-gray-300 pt-14 px-6">
      <div className="max-w-7xl mx-auto">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-10">
          <div>
            <Link to="/" aria-label="MovieBooky home">
              <img
                src="/Logo.png"
                alt="MovieBooky"
                className="h-20 object-contain"
              />
            </Link>

            <p className="text-sm leading-relaxed">
              Discover amazing events happening near you. Fast, easy, and
              secure event ticket booking platform.
            </p>
          </div>

          <FooterColumn title="Events" links={eventLinks} />
          <FooterColumn title="My Account" links={accountLinks} />
          <FooterColumn title="Company" links={companyLinks} />
        </div>

        <div className="border-t border-gray-700 mt-10 pt-6">
          <h3 className="text-white text-lg mb-4">Popular Cities</h3>

          <div className="flex flex-wrap gap-4 text-sm text-gray-400">
            {popularCities.map((city) => (
              <Link
                key={city.label}
                to={city.to}
                className="hover:text-[#ff6b00] transition"
              >
                {city.label}
              </Link>
            ))}
          </div>
        </div>

        <div className="mt-10 py-4 border-t border-gray-700">
          <div className="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between">
            <p className="text-xs text-gray-400 mb-3 md:mb-0">
              (c) {new Date().getFullYear()} MovieBooky. All Rights Reserved
            </p>

            <div className="flex items-center gap-3">
              {socialLinks.map(({ label, href, Icon }) => (
                <a
                  key={label}
                  href={href}
                  target="_blank"
                  rel="noreferrer"
                  aria-label={label}
                  className="w-9 h-9 flex items-center justify-center border border-gray-500 hover:border-[#ff6b00] hover:text-[#ff6b00] transition"
                >
                  <Icon size={14} />
                </a>
              ))}
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
