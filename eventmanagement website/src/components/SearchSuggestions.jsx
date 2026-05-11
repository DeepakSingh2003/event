import { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";

const API_BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:8000";

const buildQuery = (params) => {
  const query = new URLSearchParams();

  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== "") {
      query.set(key, value);
    }
  });

  return query.toString();
};

const Section = ({ title, children }) => (
  <div className="py-1">
    <p className="px-4 py-1 text-[11px] font-bold uppercase tracking-wider text-gray-400">
      {title}
    </p>
    {children}
  </div>
);

const ResultButton = ({ title, subtitle, onClick }) => (
  <button
    type="button"
    onMouseDown={(event) => event.preventDefault()}
    onClick={onClick}
    className="w-full px-4 py-2.5 text-left hover:bg-gray-50 flex items-start gap-3"
  >
    <span className="mt-1 h-2 w-2 rounded-full bg-[#ff6b00] shrink-0" />
    <span className="min-w-0">
      <span className="block text-sm font-semibold text-gray-800 truncate">
        {title}
      </span>
      {subtitle && (
        <span className="block text-xs text-gray-500 truncate">{subtitle}</span>
      )}
    </span>
  </button>
);

const SearchSuggestions = ({ query, selectedCountry, onPick, align = "left" }) => {
  const navigate = useNavigate();
  const [results, setResults] = useState({
    events: [],
    categories: [],
    cities: [],
    countries: [],
  });
  const [loading, setLoading] = useState(false);

  const cleanQuery = query.trim();

  useEffect(() => {
    if (cleanQuery.length < 2) {
      return;
    }

    const controller = new AbortController();
    const timer = setTimeout(async () => {
      setLoading(true);

      try {
        const countryId = selectedCountry?.id;
        const [eventsRes, categoriesRes, citiesRes, countriesRes] =
          await Promise.all([
            fetch(
              `${API_BASE_URL}/api/events?${buildQuery({
                search: cleanQuery,
                country_id: countryId,
              })}`,
              { signal: controller.signal, headers: { Accept: "application/json" } },
            ),
            fetch(`${API_BASE_URL}/api/categories?search=${encodeURIComponent(cleanQuery)}`, {
              signal: controller.signal,
              headers: { Accept: "application/json" },
            }),
            fetch(
              `${API_BASE_URL}/api/cities?${buildQuery({
                search: cleanQuery,
                country_id: countryId,
              })}`,
              { signal: controller.signal, headers: { Accept: "application/json" } },
            ),
            fetch(`${API_BASE_URL}/api/countries?search=${encodeURIComponent(cleanQuery)}`, {
              signal: controller.signal,
              headers: { Accept: "application/json" },
            }),
          ]);

        const [eventsJson, categoriesJson, citiesJson, countriesJson] =
          await Promise.all([
            eventsRes.ok ? eventsRes.json() : { data: [] },
            categoriesRes.ok ? categoriesRes.json() : { data: [] },
            citiesRes.ok ? citiesRes.json() : { data: [] },
            countriesRes.ok ? countriesRes.json() : { data: [] },
          ]);

        setResults({
          events: (eventsJson.data || []).slice(0, 4),
          categories: (categoriesJson.data || []).slice(0, 3),
          cities: (citiesJson.data || []).slice(0, 3),
          countries: (countriesJson.data || []).slice(0, 3),
        });
      } catch (error) {
        if (error.name !== "AbortError") {
          console.error("Search suggestion error:", error);
        }
      } finally {
        if (!controller.signal.aborted) {
          setLoading(false);
        }
      }
    }, 250);

    return () => {
      clearTimeout(timer);
      controller.abort();
    };
  }, [cleanQuery, selectedCountry]);

  const totalResults = useMemo(
    () =>
      results.events.length +
      results.categories.length +
      results.cities.length +
      results.countries.length,
    [results],
  );

  if (cleanQuery.length < 2) {
    return null;
  }

  const pick = (path) => {
    onPick?.();
    navigate(path);
  };

  return (
    <div
      className={`absolute top-full mt-2 w-full min-w-[280px] max-h-[420px] overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-2xl z-[80] text-gray-900 ${
        align === "right" ? "right-0" : "left-0"
      }`}
    >
      {loading && (
        <p className="px-4 py-3 text-sm font-medium text-gray-500">
          Searching...
        </p>
      )}

      {!loading && totalResults === 0 && (
        <p className="px-4 py-3 text-sm font-medium text-gray-500">
          No results found.
        </p>
      )}

      {!loading && results.events.length > 0 && (
        <Section title="Events">
          {results.events.map((event) => {
            const venue = event.primary_listing?.venue;
            return (
              <ResultButton
                key={`event-${event.id}`}
                title={event.title}
                subtitle={venue?.city || event.category || "Event"}
                onClick={() => pick(`/event/${event.slug || event.id}`)}
              />
            );
          })}
        </Section>
      )}

      {!loading && results.categories.length > 0 && (
        <Section title="Categories">
          {results.categories.map((category) => (
            <ResultButton
              key={`category-${category.id}`}
              title={category.name}
              subtitle="Category"
              onClick={() =>
                pick(
                  `/search?category_id=${category.id}&label=${encodeURIComponent(category.name)}`,
                )
              }
            />
          ))}
        </Section>
      )}

      {!loading && results.cities.length > 0 && (
        <Section title="Cities">
          {results.cities.map((city) => (
            <ResultButton
              key={`city-${city.id}`}
              title={city.name}
              subtitle={city.country_name || city.country || "City"}
              onClick={() =>
                pick(`/city/${city.slug || city.id}`)
              }
            />
          ))}
        </Section>
      )}

      {!loading && results.countries.length > 0 && (
        <Section title="Countries">
          {results.countries.map((country) => (
            <ResultButton
              key={`country-${country.id}`}
              title={country.name}
              subtitle={`${country.cities_count || 0}+ Cities`}
              onClick={() =>
                pick(
                  `/search?country_id=${country.id}&label=${encodeURIComponent(country.name)}`,
                )
              }
            />
          ))}
        </Section>
      )}

      {!loading && totalResults > 0 && (
        <button
          type="button"
          onMouseDown={(event) => event.preventDefault()}
          onClick={() => pick(`/search?q=${encodeURIComponent(cleanQuery)}`)}
          className="w-full border-t border-gray-100 px-4 py-3 text-left text-sm font-bold text-[#ff6b00] hover:bg-orange-50"
        >
          View all results for "{cleanQuery}"
        </button>
      )}
    </div>
  );
};

export default SearchSuggestions;
