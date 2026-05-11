const sections = [
  ["Information we collect", "We collect account, booking, event preference, and support information needed to run the platform."],
  ["How it is used", "Information is used for ticket booking, payment confirmation, event discovery, support, and platform safety."],
  ["Your choices", "You can update this page later with your complete legal policy, retention rules, and user rights."],
];

const PrivacyPolicyPage = () => (
  <main className="min-h-screen bg-white mt-20 text-gray-900">
    <section className="bg-[#f7f8fb] px-4 py-20 md:py-28">
      <div className="mx-auto max-w-7xl">
        <p className="text-sm font-bold uppercase tracking-[0.24em] text-[#ff6b00]">
          Legal
        </p>
        <h1 className="mt-5 text-5xl font-black md:text-7xl">Privacy Policy</h1>
        <p className="mt-6 max-w-2xl text-lg leading-8 text-gray-600">
          A clear place to explain how MovieBooky handles user and booking data.
        </p>
      </div>
    </section>

    <section className="px-4 py-16">
      <div className="mx-auto max-w-4xl divide-y divide-gray-200 border-y border-gray-200">
        {sections.map(([title, text]) => (
          <div key={title} className="py-8">
            <h2 className="text-2xl font-black">{title}</h2>
            <p className="mt-3 leading-7 text-gray-600">{text}</p>
          </div>
        ))}
      </div>
    </section>
  </main>
);

export default PrivacyPolicyPage;
