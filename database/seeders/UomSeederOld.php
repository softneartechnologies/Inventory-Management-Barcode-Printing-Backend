<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
// use Illuminate\Database\Seeder;

// database/seeders/UomSeeder.php
// use App\Models\UomCategory;
// use App\Models\UomUnit;

use Illuminate\Database\Seeder;
use App\Models\UomCategory;
use App\Models\UomUnit;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\PhysicalQuantity\Time;
use PhpUnitsOfMeasure\PhysicalQuantity\Temperature;
use PhpUnitsOfMeasure\PhysicalQuantity\Volume;
use PhpUnitsOfMeasure\PhysicalQuantity\Speed;
use PhpUnitsOfMeasure\PhysicalQuantity\Pressure;
use PhpUnitsOfMeasure\PhysicalQuantity\Force;
use PhpUnitsOfMeasure\PhysicalQuantity\Power;
use PhpUnitsOfMeasure\PhysicalQuantity\Angle;
use PhpUnitsOfMeasure\PhysicalQuantity\ElectricCurrent;
use PhpUnitsOfMeasure\PhysicalQuantity\ElectricCharge;
use PhpUnitsOfMeasure\PhysicalQuantity\Voltage;
use PhpUnitsOfMeasure\PhysicalQuantity\Resistance;
use PhpUnitsOfMeasure\PhysicalQuantity\Capacitance;
use PhpUnitsOfMeasure\PhysicalQuantity\Inductance;
use PhpUnitsOfMeasure\PhysicalQuantity\LuminousIntensity;
use PhpUnitsOfMeasure\PhysicalQuantity\LuminousFlux;
use PhpUnitsOfMeasure\PhysicalQuantity\Illuminance;
use PhpUnitsOfMeasure\PhysicalQuantity\Radioactivity;
use PhpUnitsOfMeasure\PhysicalQuantity\CatalyticActivity;
use PhpUnitsOfMeasure\PhysicalQuantity\AmountOfSubstance;
use PhpUnitsOfMeasure\PhysicalQuantity\SolidAngle;
use PhpUnitsOfMeasure\PhysicalQuantity\Information;
use PhpUnitsOfMeasure\PhysicalQuantity\Density;
use PhpUnitsOfMeasure\PhysicalQuantity\Torque;
use PhpUnitsOfMeasure\PhysicalQuantity\Acceleration;
use PhpUnitsOfMeasure\PhysicalQuantity\Velocity;
use PhpUnitsOfMeasure\PhysicalQuantity\FlowRate;
use PhpUnitsOfMeasure\PhysicalQuantity\Concentration;
use PhpUnitsOfMeasure\PhysicalQuantity\SoundPressure;
use PhpUnitsOfMeasure\PhysicalQuantity\MagneticFlux;
use PhpUnitsOfMeasure\PhysicalQuantity\MagneticFluxDensity;
use PhpUnitsOfMeasure\PhysicalQuantity\MagnetomotiveForce;
use PhpUnitsOfMeasure\PhysicalQuantity\MagneticFieldStrength;
use PhpUnitsOfMeasure\PhysicalQuantity\RadiationDose;
use PhpUnitsOfMeasure\PhysicalQuantity\RadiationDoseRate;
use PhpUnitsOfMeasure\PhysicalQuantity\ThermalConductivity;
use PhpUnitsOfMeasure\PhysicalQuantity\ThermalResistance;
use PhpUnitsOfMeasure\PhysicalQuantity\ThermalCapacity;
use PhpUnitsOfMeasure\PhysicalQuantity\SpecificHeatCapacity;
use PhpUnitsOfMeasure\PhysicalQuantity\Entropy;
use PhpUnitsOfMeasure\PhysicalQuantity\Enthalpy;
use PhpUnitsOfMeasure\PhysicalQuantity\GibbsEnergy;
use PhpUnitsOfMeasure\PhysicalQuantity\HelmholtzEnergy;
use PhpUnitsOfMeasure\PhysicalQuantity\ChemicalPotential;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarMass;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarVolume;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarConcentration;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnergy;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEntropy;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnthalpy;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarGibbsEnergy;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarHelmholtzEnergy;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarChemicalPotential;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarVolumeFlowRate;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarMassFlowRate;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnergyFlowRate;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEntropyFlowRate;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnthalpyFlowRate;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarGibbsEnergyFlowRate;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarHelmholtzEnergyFlowRate;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarChemicalPotentialFlowRate;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarVolumeFlowRatePerUnitArea;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarMassFlowRatePerUnitArea;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnergyFlowRatePerUnitArea;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEntropyFlowRatePerUnitArea;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnthalpyFlowRatePerUnitArea;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarGibbsEnergyFlowRatePerUnitArea;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarHelmholtzEnergyFlowRatePerUnitArea;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarChemicalPotentialFlowRatePerUnitArea;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarVolumeFlowRatePerUnitTime;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarMassFlowRatePerUnitTime;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnergyFlowRatePerUnitTime;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEntropyFlowRatePerUnitTime;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnthalpyFlowRatePerUnitTime;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarGibbsEnergyFlowRatePerUnitTime;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarHelmholtzEnergyFlowRatePerUnitTime;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarChemicalPotentialFlowRatePerUnitTime;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarVolumeFlowRatePerUnitMass;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarMassFlowRatePerUnitMass;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnergyFlowRatePerUnitMass;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEntropyFlowRatePerUnitMass;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnthalpyFlowRatePerUnitMass;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarGibbsEnergyFlowRatePerUnitMass;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarHelmholtzEnergyFlowRatePerUnitMass;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarChemicalPotentialFlowRatePerUnitMass;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarVolumeFlowRatePerUnitVolume;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarMassFlowRatePerUnitVolume;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnergyFlowRatePerUnitVolume;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEntropyFlowRatePerUnitVolume;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnthalpyFlowRatePerUnitVolume;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarGibbsEnergyFlowRatePerUnitVolume;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarHelmholtzEnergyFlowRatePerUnitVolume;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarChemicalPotentialFlowRatePerUnitVolume;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarVolumeFlowRatePerUnitAmount;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarMassFlowRatePerUnitAmount;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnergyFlowRatePerUnitAmount;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEntropyFlowRatePerUnitAmount;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnthalpyFlowRatePerUnitAmount;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarGibbsEnergyFlowRatePerUnitAmount;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarHelmholtzEnergyFlowRatePerUnitAmount;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarChemicalPotentialFlowRatePerUnitAmount;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarVolumeFlowRatePerUnitMole;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarMassFlowRatePerUnitMole;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnergyFlowRatePerUnitMole;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEntropyFlowRatePerUnitMole;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnthalpyFlowRatePerUnitMole;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarGibbsEnergyFlowRatePerUnitMole;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarHelmholtzEnergyFlowRatePerUnitMole;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarChemicalPotentialFlowRatePerUnitMole;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarVolumeFlowRatePerUnitParticle;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarMassFlowRatePerUnitParticle;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnergyFlowRatePerUnitParticle;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEntropyFlowRatePerUnitParticle;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnthalpyFlowRatePerUnitParticle;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarGibbsEnergyFlowRatePerUnitParticle;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarHelmholtzEnergyFlowRatePerUnitParticle;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarChemicalPotentialFlowRatePerUnitParticle;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarVolumeFlowRatePerUnitEntity;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarMassFlowRatePerUnitEntity;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnergyFlowRatePerUnitEntity;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEntropyFlowRatePerUnitEntity;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnthalpyFlowRatePerUnitEntity;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarGibbsEnergyFlowRatePerUnitEntity;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarHelmholtzEnergyFlowRatePerUnitEntity;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarChemicalPotentialFlowRatePerUnitEntity;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarVolumeFlowRatePerUnitAtom;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarMassFlowRatePerUnitAtom;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnergyFlowRatePerUnitAtom;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEntropyFlowRatePerUnitAtom;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarEnthalpyFlowRatePerUnitAtom;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarGibbsEnergyFlowRatePerUnitAtom;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarHelmholtzEnergyFlowRatePerUnitAtom;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarChemicalPotentialFlowRatePerUnitAtom;
use PhpUnitsOfMeasure\PhysicalQuantity\MolarVolumeFlowRatePerUnitMolecule;

class UomSeeder extends Seeder
{
    public function run()
    {
        // Array of available quantity classes from php-units-of-measure
        $quantityClasses = [
            'Length' => Length::class,
            'Mass' => Mass::class,
            'Time' => Time::class,
            'Temperature' => Temperature::class,
            'Volume' => Volume::class,
            'Speed' => Speed::class,
            'Pressure' => Pressure::class,
            'Force' => Force::class,
            'Power' => Power::class,
            'Angle' => Angle::class,
            'ElectricCurrent' => ElectricCurrent::class,
            'ElectricCharge' => ElectricCharge::class,
            'Voltage' => Voltage::class,
            'Resistance' => Resistance::class,
            'Capacitance' => Capacitance::class,
            'Inductance' => Inductance::class,
            'LuminousIntensity' => LuminousIntensity::class,
            'LuminousFlux' => LuminousFlux::class,
            'Illuminance' => Illuminance::class,
            'Radioactivity' => Radioactivity::class,
            'CatalyticActivity' => CatalyticActivity::class,
            'AmountOfSubstance' => AmountOfSubstance::class,
            'SolidAngle' => SolidAngle::class,
            'Information' => Information::class,
            'Density' => Density::class,
            'Torque' => Torque::class,
            'Acceleration' => Acceleration::class,
            'Velocity' => Velocity::class,
            'FlowRate' => FlowRate::class,
            'Concentration' => Concentration::class,
            'SoundPressure' => SoundPressure::class,
            'MagneticFlux' => MagneticFlux::class,
            'MagneticFluxDensity' => MagneticFluxDensity::class,
            'MagnetomotiveForce' => MagnetomotiveForce::class,
            'MagneticFieldStrength' => MagneticFieldStrength::class,
            'RadiationDose' => RadiationDose::class,
            'RadiationDoseRate' => RadiationDoseRate::class,
            'ThermalConductivity' => ThermalConductivity::class,
            'ThermalResistance' => ThermalResistance::class,
            'ThermalCapacity' => ThermalCapacity::class,
            'SpecificHeatCapacity' => SpecificHeatCapacity::class,
            'Entropy' => Entropy::class,
            'Enthalpy' => Enthalpy::class,
            'GibbsEnergy' => GibbsEnergy::class,
            'HelmholtzEnergy' => HelmholtzEnergy::class,
            'ChemicalPotential' => ChemicalPotential::class,
            'MolarMass' => MolarMass::class,
            'MolarVolume' => MolarVolume::class,
            'MolarConcentration' => MolarConcentration::class,
            'MolarEnergy' => MolarEnergy::class,
            'MolarEntropy' => MolarEntropy::class,
            'MolarEnthalpy' => MolarEnthalpy::class,
            'MolarGibbsEnergy' => MolarGibbsEnergy::class,
            'MolarHelmholtzEnergy' => MolarHelmholtzEnergy::class,
            'MolarChemicalPotential' => MolarChemicalPotential::class,
            'MolarVolumeFlowRate' => MolarVolumeFlowRate::class,
            'MolarMassFlowRate' => MolarMassFlowRate::class,
            'MolarEnergyFlowRate' => MolarEnergyFlowRate::class,
            'MolarEntropyFlowRate' => MolarEntropyFlowRate::class,
            'MolarEnthalpyFlowRate' => MolarEnthalpyFlowRate::class,
            'MolarGibbsEnergyFlowRate' => MolarGibbsEnergyFlowRate::class,
            'MolarHelmholtzEnergyFlowRate' => MolarHelmholtzEnergyFlowRate::class,
            'MolarChemicalPotentialFlowRate' => MolarChemicalPotentialFlowRate::class,
        ];

        // Loop through each physical quantity (Length, Mass, etc.)
        foreach ($quantityClasses as $categoryName => $quantityClass) {
            // Create or find the category by name
            $category = UomCategory::firstOrCreate(['name' => $categoryName]);

            // Get all unit definitions for this quantity (Length, Mass, etc.)
            // Pass both a value and a unit
            $instance = new $quantityClass(1, 'm'); // Assuming 'm' for Length, 'kg' for Mass, etc.
            $units = $instance->getUnitDefinitions(); // Get all unit definitions

            // Loop through each unit and insert them into UomUnit table
            foreach ($units as $unit) {
                
                // Insert unit into the database under the appropriate category
                UomUnit::firstOrCreate([
                    'uom_category_id' => $category->id,
                    'unit_name' => $unit->getUnitName(),
                    'abbreviation' => $unit->getUnitSymbol(),
                ]);
            }
        }

        // foreach ($quantityClasses as $categoryName => $units) {
        //     $category = UomCategory::create(['name' => $categoryName]);

        //     foreach ($units as $unit) {
        //         UomUnit::create([
        //             'uom_category_id' => $category->id,
        //             'unit_name' => $unit['name'],
        //             'abbreviation' => $unit['abbreviation'],
        //         ]);
        //     }
        // }
    }
}
