package dido.pdfmanager;

public class Signature {
	private String name;
	private int revision;
	private int totalREvisions;
	private boolean complete;
	private boolean invisible;
	private String hashAlgorithm;
	private String encryptionAlgorithm;
	private String filterSubtype;
	
	private String signer;
	private String signerAlt;
	private String signType;
	private String signDate;
	private String publicKey;
	
	public String getName() {
		return name;
	}
	
	public void setName(String name) {
		this.name = name;
	}
	
	public int getRevision() {
		return revision;
	}

	public void setRevision(int revision) {
		this.revision = revision;
	}

	public int getTotalREvisions() {
		return totalREvisions;
	}

	public void setTotalREvisions(int totalREvisions) {
		this.totalREvisions = totalREvisions;
	}

	public boolean isComplete() {
		return complete;
	}

	public void setComplete(boolean complete) {
		this.complete = complete;
	} 
	
	public boolean isInvisible() {
		return invisible;
	}

	public void setInvisible(boolean invisible) {
		this.invisible = invisible;
	}

	public String getHashAlgorithm() {
		return hashAlgorithm;
	}

	public void setHashAlgorithm(String hashAlgorithm) {
		this.hashAlgorithm = hashAlgorithm;
	}

	public String getEncryptionAlgorithm() {
		return encryptionAlgorithm;
	}

	public void setEncryptionAlgorithm(String encryptionAlgorithm) {
		this.encryptionAlgorithm = encryptionAlgorithm;
	}

	public String getFilterSubtype() {
		return filterSubtype;
	}

	public void setFilterSubtype(String filterSubtype) {
		this.filterSubtype = filterSubtype;
	}

	public String getSigner() {
		return signer;
	}

	public void setSigner(String signer) {
		this.signer = signer;
	}

	public String getSignerAlt() {
		return signerAlt;
	}

	public void setSignerAlt(String signerAlt) {
		this.signerAlt = signerAlt;
	}

	public String getSignType() {
		return signType;
	}

	public void setSignType(String signType) {
		this.signType = signType;
	}

	public String getSignDate() {
		return signDate;
	}

	public void setSignDate(String signDate) {
		this.signDate = signDate;
	}
	
	public String getPublicKey() {
		return publicKey;
	}

	public void setPublicKey(String publicKey) {
		this.publicKey = publicKey;
	}

}
