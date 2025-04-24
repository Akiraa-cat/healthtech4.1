<?php
// Database connection
include 'connect.php';

// Get all doctors from database
$query = "SELECT * FROM doctors ORDER BY name ASC";
$result = $db->query($query);

// Get unique specializations for filter
$specializations = $db->query("SELECT DISTINCT specialization FROM doctors ORDER BY specialization ASC");

// Get unique hospitals for filter
$hospitals = $db->query("SELECT DISTINCT hospital FROM doctors ORDER BY hospital ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon web" href="../assets/konsulku-nobg.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konsultasi Dokter Online</title>
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color:#06b6d4;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: #f5f7fa;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 0;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px 0;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .filter-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .search-container {
            flex-grow: 1;
            max-width: 500px;
            min-width: 250px;
        }
        .search-container input {
            width: 60%;
            padding: 12px 20px;
            border: 2px solid #ddd;
            border-radius: 30px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s;
        }
        
        .search-container input:focus {
            border-color: var(--primary-color);
        }

        .filter-dropdown {
            min-width: 200px;
            position: relative;
        }
        
        .filter-dropdown select {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #ddd;
            border-radius: 30px;
            font-size: 1rem;
            outline: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .filter-dropdown select:focus {
            border-color: var(--primary-color);
        }
        
        .reset-filters {
            background-color: #f8f9fa;
            color: var(--dark-color);
            border: 2px solid #ddd;
            padding: 12px 20px;
            border-radius: 30px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .reset-filters:hover {
            background-color: #e9ecef;
        }
        
        .doctor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .doctor-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .card-image-container {
            width: 100%;
            height: 250px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
        }
        
        .doctor-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center top;
            transition: transform 0.3s ease;
        }
        
        .doctor-card:hover .doctor-image {
            transform: scale(1.03);
        }
        
        .doctor-info {
            padding: 20px;
        }
        
        .doctor-name {
            font-size: 1.4rem;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .doctor-specialty {
            font-weight: bold;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }
        
        .doctor-hospital {
            color: #666;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        
        .doctor-bio {
            margin-bottom: 15px;
            color: #555;
            line-height: 1.5;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: 2px solid var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #005f92;
            border-color: #005f92;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .modal-content {
            background-color: white;
            margin: 50px auto;
            width: 80%;
            max-width: 900px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
            animation: modalFadeIn 0.4s;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            padding: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            font-size: 1.8rem;
            margin: 0;
        }
        
        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 5px;
        }
        
        .modal-body {
            padding: 30px;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }
        
        .modal-image-container {
            width: 100%;
            max-height: 400px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .doctor-details-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            object-position: center;
            border-radius: 8px;
        }
        
        .detail-section {
            margin-bottom: 20px;
        }
        
        .detail-section h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
            border-bottom: 2px solid #eee;
            padding-bottom: 5px;
        }
        
        .detail-item {
            margin-bottom: 8px;
            display: flex;
        }
        
        .detail-label {
            font-weight: bold;
            min-width: 120px;
        }
        
        .education-item, .experience-item {
            margin-bottom: 10px;
        }
        
        .education-item h4, .experience-item h4 {
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        @media (max-width: 768px) {
            .modal-body {
                grid-template-columns: 1fr;
            }
            
            .doctor-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-container,
            .filter-dropdown {
                max-width: 100%;
                min-width: auto;
            }
            
            .card-image-container {
                height: 200px;
            }
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .back-home-btn {
            background-color: white;
            color: var(--primary-color);
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            display: inline-block;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .back-home-btn:hover {
            background-color: #f1f1f1;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        /* Add responsive styling for the header */
        @media (max-width: 768px) {
            .header-flex {
                flex-direction: column;
                gap: 20px;
            }
            
            .back-home-btn {
                padding: 8px 16px;
            }
        }

        @media (max-width: 576px) {
            header h1 {
                font-size: 2rem;
            }
            
            header p {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-flex">
                <div>
                    <h1>Konsultasi Dokter Online</h1>
                    <p>Temukan dokter profesional untuk kebutuhan kesehatan Anda</p>
                </div>
                <a href="../index.php" class="back-home-btn">Kembali ke Beranda</a>
            </div>
        </div>
    </header>   
    
    <div class="container">
        <div class="filter-container">
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Cari dokter berdasarkan nama...">
            </div>
            
            <div class="filter-dropdown">
                <select id="specializationFilter">
                    <option value="">Semua Spesialisasi</option>
                    <?php while($spec = $specializations->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($spec['specialization']); ?>">
                            <?php echo htmlspecialchars($spec['specialization']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="filter-dropdown">
                <select id="hospitalFilter">
                    <option value="">Semua Rumah Sakit</option>
                    <?php while($hosp = $hospitals->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($hosp['hospital']); ?>">
                            <?php echo htmlspecialchars($hosp['hospital']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <button class="reset-filters" id="resetFilters">Reset Filter</button>
        </div>
        
        <div class="doctor-grid" id="doctorGrid">
            <?php while($doctor = $result->fetch_assoc()): ?>
                <div class="doctor-card" 
                     data-id="<?php echo $doctor['id']; ?>"
                     data-specialization="<?php echo htmlspecialchars($doctor['specialization']); ?>"
                     data-hospital="<?php echo htmlspecialchars($doctor['hospital']); ?>">
                    <div class="card-image-container">
                        <img src="<?php echo $doctor['photo'] ?: 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80'; ?>" 
                             alt="<?php echo htmlspecialchars($doctor['name']); ?>" 
                             class="doctor-image"
                             loading="lazy">
                    </div>
                    <div class="doctor-info">
                        <h3 class="doctor-name"><?php echo htmlspecialchars($doctor['name']); ?></h3>
                        <p class="doctor-specialty"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                        <p class="doctor-hospital"><?php echo htmlspecialchars($doctor['hospital']); ?></p>
                        <p class="doctor-bio"><?php echo substr(htmlspecialchars($doctor['bio']), 0, 100); ?>...</p>
                        <a href="#" class="btn btn-primary view-detail" data-doctor-id="<?php echo $doctor['id']; ?>">Selengkapnya</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <!-- Doctor Detail Modal -->
    <div id="doctorModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Detail Dokter</h2>
                <button class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div>
                    <div class="modal-image-container">
                        <img id="modalDoctorImage" src="" alt="Doctor Photo" class="doctor-details-img">
                    </div>
                    <div class="detail-section" style="margin-top: 20px;">
                        <h3>Informasi Praktik</h3>
                        <div class="detail-item">
                            <span class="detail-label">Biaya Konsul:</span>
                            <span id="modalConsultationFee"></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Hari Praktik:</span>
                            <span id="modalAvailableDays"></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email:</span>
                            <span id="modalEmail"></span>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="detail-section">
                        <h3>Spesialisasi</h3>
                        <p id="modalSpecialty"></p>
                    </div>
                    <div class="detail-section">
                        <h3>Rumah Sakit/Klinik</h3>
                        <p id="modalHospital"></p>
                    </div>
                    <div class="detail-section">
                        <h3>Tentang Dokter</h3>
                        <p id="modalBio"></p>
                    </div>
                    <div class="detail-section">
                        <h3>Pendidikan</h3>
                        <div id="modalEducation"></div>
                    </div>
                    <div class="detail-section">
                        <h3>Pengalaman</h3>
                        <div id="modalExperience"></div>
                    </div>
                    <div class="detail-section">
                        <h3>Penghargaan</h3>
                        <div id="modalAwards"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("Script initialized"); // Debug confirmation
    
    // Get essential elements
    const modal = document.getElementById('doctorModal');
    const closeBtn = document.querySelector('.close-btn');
    const searchInput = document.getElementById('searchInput');
    const specializationFilter = document.getElementById('specializationFilter');
    const hospitalFilter = document.getElementById('hospitalFilter');
    const resetFilters = document.getElementById('resetFilters');
    const doctorCards = document.querySelectorAll('.doctor-card');
    
    // Check if essential elements exist
    if (!modal || !closeBtn) {
        console.error("Critical elements not found!");
        return;
    }

    // Modal close functionality
    closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Function to filter doctors
    function filterDoctors() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedSpecialization = specializationFilter.value;
        const selectedHospital = hospitalFilter.value;
        
        doctorCards.forEach(card => {
            const name = card.querySelector('.doctor-name').textContent.toLowerCase();
            const specialization = card.getAttribute('data-specialization');
            const hospital = card.getAttribute('data-hospital');
            const cardText = card.textContent.toLowerCase();
            
            const matchesSearch = searchTerm === '' || cardText.includes(searchTerm);
            const matchesSpecialization = selectedSpecialization === '' || specialization === selectedSpecialization;
            const matchesHospital = selectedHospital === '' || hospital === selectedHospital;
            
            if (matchesSearch && matchesSpecialization && matchesHospital) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    // Event listeners for filters
    searchInput.addEventListener('input', filterDoctors);
    specializationFilter.addEventListener('change', filterDoctors);
    hospitalFilter.addEventListener('change', filterDoctors);
    
    // Reset filters
    resetFilters.addEventListener('click', function() {
        searchInput.value = '';
        specializationFilter.value = '';
        hospitalFilter.value = '';
        filterDoctors();
    });

    // Enhanced "Selengkapnya" button handling using event delegation
    document.addEventListener('click', async function(e) {
        const detailBtn = e.target.closest('.view-detail');
        
        if (detailBtn) {
            e.preventDefault();
            console.log("Detail button clicked"); // Debug
            
            const doctorId = detailBtn.getAttribute('data-doctor-id');
            if (!doctorId) {
                console.error("Missing doctor ID");
                return;
            }

            // Show loading state
            const originalText = detailBtn.textContent;
            detailBtn.textContent = "Memuat...";
            detailBtn.style.pointerEvents = "none";

            try {
                console.log(`Fetching data for doctor ID: ${doctorId}`); // Debug
                
                const response = await fetch(`get_doctor.php?id=${doctorId}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                
                const doctor = await response.json();
                console.log("Received doctor data:", doctor); // Debug
                
                // Populate modal with doctor data
                populateModal(doctor);
                modal.style.display = "block";
                
            } catch (error) {
                console.error("Error fetching doctor details:", error);
                alert("Gagal memuat data dokter. Silakan coba lagi.");
            } finally {
                // Restore button state
                detailBtn.textContent = originalText;
                detailBtn.style.pointerEvents = "auto";
            }
        }
    });

    // Function to populate modal data
    function populateModal(doctor) {
        // Basic info
        document.querySelector('.modal-title').textContent = doctor.name || "Data tidak tersedia";
        
        // Handle doctor image
        const doctorImage = document.getElementById('modalDoctorImage');
        doctorImage.src = doctor.photo || 'https://via.placeholder.com/400x500?text=Doctor+Photo';
        
        // Auto adjust image display based on orientation
        doctorImage.onload = function() {
            if (this.naturalWidth > this.naturalHeight) {
                // Landscape image
                this.style.width = '100%';
                this.style.height = 'auto';
            } else {
                // Portrait image
                this.style.width = 'auto';
                this.style.height = '80%';
            }
        };
        
        // Rest of the doctor data
        document.getElementById('modalSpecialty').textContent = doctor.specialization || "-";
        document.getElementById('modalHospital').textContent = doctor.hospital || "-";
        document.getElementById('modalBio').textContent = doctor.bio || "-";
        document.getElementById('modalConsultationFee').textContent = doctor.consultation_fee ? `Rp${doctor.consultation_fee.toLocaleString('id-ID')}` : "-";
        document.getElementById('modalAvailableDays').textContent = doctor.available_days || "-";
        document.getElementById('modalEmail').textContent = doctor.email || "-";
        
        // Education
        const educationContainer = document.getElementById('modalEducation');
        educationContainer.innerHTML = '';
        if (doctor.education) {
            doctor.education.split('\n').forEach(edu => {
                if (edu.trim()) {
                    const [institution, period] = edu.split(')');
                    const div = document.createElement('div');
                    div.className = 'education-item';
                    div.innerHTML = `<h4>${institution})</h4><p>${period?.trim() || ''}</p>`;
                    educationContainer.appendChild(div);
                }
            });
        }
        
        // Experience
        const experienceContainer = document.getElementById('modalExperience');
        experienceContainer.innerHTML = '';
        if (doctor.experience) {
            doctor.experience.split('\n').forEach(exp => {
                if (exp.trim()) {
                    const [position, period] = exp.split(')');
                    const div = document.createElement('div');
                    div.className = 'experience-item';
                    div.innerHTML = `<h4>${position})</h4><p>${period?.trim() || ''}</p>`;
                    experienceContainer.appendChild(div);
                }
            });
        }
        
        // Awards
        const awardsContainer = document.getElementById('modalAwards');
        awardsContainer.innerHTML = '';
        if (doctor.awards) {
            doctor.awards.split('\n').forEach(award => {
                if (award.trim()) {
                    const p = document.createElement('p');
                    p.textContent = `• ${award.trim()}`;
                    awardsContainer.appendChild(p);
                }
            });
        }
    }
});
    </script>
</body>
</html>