const terms = [
  ["Platform usage", "Customers agree to use MovieBooky for lawful event discovery and ticket booking."],
  ["Bookings", "Ticket changes, entry rules, cancellations, and refunds depend on each event and organizer policy."],
  ["Payments", "Payment status and booking confirmation must be completed before ticket access is granted."],
  ["Updates", "Replace this starter content with your final legal terms when your policy is ready."],
];

const TermsPage = () => (
  <main className="min-h-screen bg-white mt-20 text-gray-900">
    <section className="bg-[#111827] px-4 py-20 text-white md:py-28">
      <div className="mx-auto max-w-7xl">
        <p className="text-sm font-bold uppercase tracking-[0.24em] text-orange-300">
          Legal
        </p>
        <h1 className="mt-5 text-5xl font-black md:text-7xl">Terms & Conditions</h1>
        <p className="mt-6 max-w-2xl text-lg leading-8 text-white/70">
          The terms page is ready for your platform, booking, and refund rules.
        </p>
      </div>
    </section>

    <section className="px-4 py-16">
      <div className="mx-auto max-w-4xl">
        {terms.map(([title, text], index) => (
          <div key={title} className="grid gap-4 border-b border-gray-200 py-8 md:grid-cols-[100px_1fr]">
            <span className="text-sm font-black text-[#ff6b00]">0{index + 1}</span>
            <div>
              <h2 className="text-2xl font-black">{title}</h2>
              <p className="mt-3 leading-7 text-gray-600">{text}</p>
            </div>
          </div>
        ))}
      </div>
    </section>
  </main>
);

export default TermsPage;
