const contactBlocks = [
  ["Support", "support@moviebooky.com", "Booking, ticket, payment, and account help."],
  ["Partnerships", "partners@moviebooky.com", "Event organizers, venues, and brand collaborations."],
  ["Office Hours", "10:00 AM - 7:00 PM IST", "Our team replies during working hours."],
];

const ContactPage = () => (
  <main className="min-h-screen bg-white mt-20 text-gray-900">
    <section className="bg-[#111827] px-4 py-20 text-white md:py-28">
      <div className="mx-auto max-w-7xl">
        <p className="text-sm font-bold uppercase tracking-[0.24em] text-orange-300">
          Contact
        </p>
        <h1 className="mt-5 max-w-4xl text-5xl font-black leading-tight md:text-7xl">
          We are here to help your plans go right
        </h1>
        <p className="mt-6 max-w-2xl text-lg leading-8 text-white/70">
          Reach out for booking support, organizer questions, refunds, payments,
          or partnership conversations.
        </p>
      </div>
    </section>

    <section className="px-4 py-20">
      <div className="mx-auto grid max-w-7xl gap-8 md:grid-cols-3">
        {contactBlocks.map(([title, value, text]) => (
          <div key={title} className="border-t-4 border-[#ff6b00] pt-6">
            <h2 className="text-2xl font-black">{title}</h2>
            <p className="mt-3 font-bold text-gray-900">{value}</p>
            <p className="mt-3 leading-7 text-gray-600">{text}</p>
          </div>
        ))}
      </div>
    </section>
  </main>
);

export default ContactPage;
