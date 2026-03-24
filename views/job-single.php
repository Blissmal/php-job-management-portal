<?php include_once 'partials/header.php'; ?>

<?php
// ── Mock Job Data ──────────────────────────────────────────────────────────────
$job = [
    'title'         => 'Tutorial Fellow – Information Technology',
    'type'          => 'Full Time',
    'location'      => 'Nairobi',
    'posted'        => '6 hours ago',
    'deadline'      => 'May 30, 2025',
    'career_level'  => 'Lead, Manager, Senior',
    'industry'      => 'Education & Training',
    'experience'    => '3-5 Years',
    'qualification' => 'Bachelor Degree, Master Degree',
    'employer' => [
        'name'    => 'Zetech University',
        'tagline' => 'Inventing the future',
        'website' => 'https://www.zetech.ac.ke',
        'logo'    => 'ZU',
        'logo_bg' => '#1a2a6c',
        'logo_color' => '#fff',
    ],
    'objective' => 'The purpose of this position is to carry out teaching roles, student mentoring, research and other duties in liaison with the university\'s overall goal.',
    'duties' => [
        'To teach in areas allocated by the Head of Department.',
        'Designing, developing, planning and delivering a range of programmes of study, sometimes for entirely new courses at various levels.',
        'Delivering high-quality lectures and practicals.',
        'Reviewing course content and materials on a regular basis, updating when required.',
        'Collaborating with academic colleagues on course development, curriculum changes and the development of research activities.',
        'Carrying out research and writing research grant proposals.',
        'Seeking and participating in consultancies.',
        'Presenting research papers at national or international conferences and other similar events.',
        'Dissemination of research outputs.',
        'Providing mentorship and career guidance to students and staff.',
        'Any other duties as may be assigned from time to time by the supervisors or as captured in your detailed job description.',
    ],
    'qualifications' => [
        'A Bachelor\'s degree and a Master\'s degree qualification in Computer Science, Information Technology from a recognized/accredited University in the relevant field.',
        'At least three (3) years of post-qualification work experience.',
        'Demonstrated potential for university teaching and research.',
        'Commitment to high-quality teaching and fostering a positive learning environment for students.',
    ],
    'note' => 'Interviews will be held on a rolling basis',
];

$overview_items = [
    ['icon' => 'bar-chart-2',  'label' => 'Career Level',  'value' => $job['career_level']],
    ['icon' => 'building-2',   'label' => 'Industry',       'value' => $job['industry']],
    ['icon' => 'sliders',      'label' => 'Experience',     'value' => $job['experience']],
    ['icon' => 'graduation-cap', 'label' => 'Qualification', 'value' => $job['qualification']],
];
?>

<main class="w-full">
    <section class="relative w-full overflow-hidden" style="height:280px;">
        <div class="absolute inset-0" style="background: linear-gradient(135deg, #8b91dd 0%, #10195d 70%, #10195d 100%); opacity:0.92;"></div>
        <div class="absolute inset-0 flex flex-col items-center justify-end pb-10 z-10">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-3 tracking-tight">
                <?php echo htmlspecialchars($job['title']); ?>
            </h1>
            <p class="text-gray-300 text-sm flex items-center gap-2 justify-center">
                <span class="border border-white py-1 px-4 rounded-full text-sm"><?php echo $job['type']; ?></span>
                <span class="flex items-center gap-2">
                    <i data-lucide="map-pin" class="h-4 w-4"></i>
                    <?php echo $job['location']; ?>
                </span>
                <span class="flex items-center gap-2">
                    <i data-lucide="calendar" class="h-4 w-4"></i>
                    <?php echo $job['posted']; ?>
                </span>
            </p>
        </div>
    </section>


    <div class="min-h-screen py-8 px-4">
        <div class="max-w-6xl mx-auto flex gap-6 items-start">

            <!-- ── Left: Job Content ─────────────────────────────────────── -->
            <div class="flex-1 min-w-0">

                <!-- Employer card -->
                <div class="bg-white  border-b border-gray-200 p-6 mb-6 flex items-start gap-5">
                    <div class="shrink-0 w-20 h-20 rounded-lg border border-gray-100 flex items-center justify-center
                    text-base font-bold shadow-sm"
                        style="background-color:<?php echo $job['employer']['logo_bg']; ?>; color:<?php echo $job['employer']['logo_color']; ?>;">
                        <?php echo $job['employer']['logo']; ?>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900"><?php echo htmlspecialchars($job['employer']['name']); ?></h2>
                        <p class="text-sm text-gray-500 mt-0.5"><?php echo htmlspecialchars($job['employer']['tagline']); ?></p>
                        <a href="<?php echo $job['employer']['website']; ?>" target="_blank" rel="noopener"
                            class="flex items-center gap-1.5 mt-2 text-sm text-[#2b9a66] hover:underline">
                            <i data-lucide="link" class="w-3.5 h-3.5"></i>
                            <?php echo $job['employer']['website']; ?>
                        </a>
                    </div>
                </div>

                <!-- Job description card -->
                <div class="bg-white   p-8">

                    <!-- Job Objective -->
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Job Objective</h2>
                    <p class="text-sm text-gray-600 leading-relaxed mb-5">
                        <?php echo htmlspecialchars($job['objective']); ?>
                    </p>

                    <p class="text-sm font-bold text-gray-800 mb-3">Duties and responsibilities</p>
                    <ul class="list-none space-y-2 mb-8 pl-2">
                        <?php foreach ($job['duties'] as $duty): ?>
                            <li class="flex items-start gap-2 text-sm text-gray-600 leading-relaxed">
                                <span class="mt-1.5 w-1.5 h-1.5 rounded-full border border-gray-400 shrink-0"></span>
                                <?php echo htmlspecialchars($duty); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Qualification and experience -->
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Qualification and experience</h2>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-6">
                        <?php foreach ($job['qualifications'] as $q): ?>
                            <li class="text-sm text-gray-600 leading-relaxed"><?php echo htmlspecialchars($q); ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if ($job['note']): ?>
                        <p class="text-sm font-bold text-gray-800 border-t border-gray-100 pt-5 mt-5">
                            <?php echo htmlspecialchars($job['note']); ?>
                        </p>
                    <?php endif; ?>

                </div>
            </div>

            <!-- ── Right: Sidebar ───────────────────────────────────────── -->
            <div class="w-72 shrink-0 sticky top-24 flex flex-col gap-5">

                <!-- Apply button -->
                <a href="/jobs/<?php echo urlencode(strtolower(str_replace([' ', '–'], ['-', '-'], $job['title']))); ?>/apply"
                    class="w-full block text-center bg-[#fb236a] hover:bg-[#e01060] text-white font-semibold text-base
                py-4 rounded-lg shadow transition-colors duration-200">
                    Apply for job
                </a>

                <!-- Job Overview -->
                <div class="bg-white   px-6 py-5">
                    <h3 class="text-base font-bold text-gray-900 mb-4">Job Overview</h3>
                    <div class="flex flex-col divide-y divide-gray-100">
                        <?php foreach ($overview_items as $item): ?>
                            <div class="flex items-start gap-3.5 py-4 first:pt-0 last:pb-0">
                                <i data-lucide="<?php echo $item['icon']; ?>"
                                    class="w-6 h-6 text-[#2b9a66] shrink-0 mt-0.5" stroke-width="1.75"></i>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800"><?php echo $item['label']; ?></p>
                                    <p class="text-xs text-gray-500 mt-0.5 leading-snug"><?php echo $item['value']; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Job Location -->
                <div class="bg-white   px-6 py-5">
                    <h3 class="text-base font-bold text-gray-900 mb-4">Job Location</h3>
                    <!-- Map placeholder – swap for a real embed or Google Maps iframe -->
                    <div class="w-full h-40 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                        <div class="flex flex-col items-center gap-2 text-xs">
                            <i data-lucide="map" class="w-8 h-8 text-gray-300"></i>
                            <span><?php echo htmlspecialchars($job['location']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Deadline -->
                <div class="bg-white   px-6 py-4 flex items-center gap-3">
                    <i data-lucide="calendar-clock" class="w-5 h-5 text-[#2b9a66] shrink-0"></i>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Application Deadline</p>
                        <p class="text-sm font-semibold text-gray-800 mt-0.5"><?php echo $job['deadline']; ?></p>
                    </div>
                </div>

            </div>
        </div>
    </div>

</main>

<?php include_once 'partials/footer.php'; ?>
